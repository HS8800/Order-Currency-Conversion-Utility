<?php

namespace App\Command;

use DOMDocument;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class TestCommand extends Command
{
    protected static $defaultName = 'app:order-rate';

    public function __construct($projectDir)
    {
        $this->projectDir = $projectDir;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Order Currency Converter')
            ->addArgument('orderId', InputArgument::REQUIRED, 'Id of order to apply currency conversion')
            ->addArgument('outputCurreny', InputArgument::REQUIRED, 'Symbol of currency to convert to')
            ->addArgument('debug', InputArgument::OPTIONAL, 'Used to disable file creation for testing',false);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {   
        //load xml
        $inputFile = $this->projectDir . '\public\Orders.xml';
        $xmlOrder=simplexml_load_file($inputFile);

        $inputFile = $this->projectDir . '\public\ExchangeRates.xml';
        $xmlRates=simplexml_load_file($inputFile);

        //get params
        $orderId = $input->getArgument('orderId');
        $outputCurreny = $input->getArgument('outputCurreny');
        
        
        //find order
        $order = $this->getOrder($orderId,$xmlOrder);
        if(!$order){
            $this->Error("Failed to find order with id of " . $orderId);
        }

        //find exchange rate from the order and params
        $exRate = $this->getExRates($order[0]->currency,$outputCurreny,$order[0]->date,$xmlRates);
        if(!$exRate){
            $this->Error("Failed to find input Exchange Rate of " . $outputCurreny . " for " . $order[0]->date);
        }


        //apply exchange rate to order
        foreach ($order[0]->xpath(".//product") as $products) {
            $products->attributes()->price = ceil(($products->xpath("./@price")[0]*(double)$exRate) * 100) / 100;   
        }     

        $order[0]->currency = $outputCurreny;
        $order[0]->total = ceil(($order[0]->total*(double)$exRate) * 100) / 100;


        if(!$input->getArgument('debug')){
            //output order to xml file
            $myfile = fopen(__DIR__."/../../xmlOutput/CurrencyExchangedOrder.xml", "w") or die("Unable to open file!");
            $txt = chr(9).$order[0]->asXML();
            fwrite($myfile, $txt);
            fclose($myfile);

            //open file
            popen(__DIR__."/../../xmlOutput/CurrencyExchangedOrder.xml",'r');
        }
    
        Echo "\n";
        Echo "Order id " .$orderId." succesfully converted to " . $outputCurreny;
        Echo "\nLocation: " . __DIR__."/../../xmlOutput/CurrencyExchangedOrder.xml";
        Echo "\n";


        return Command::SUCCESS;
    }


    /**
     * Retrieve order by id from xml structure
     *
     * @param id    $id  id of the order you want to retrieve
     * @param xml   $xml XML source that will be searched through
     * 
     * @return order    returns orders with matching id
     */ 
    public function getOrder($id,$xml){
        $order = $xml->xpath("//id[number() = ".$id."]/..");
        if(empty($order)){
            return false;
        }else{
            return $order;
        }
    }


    /**
     * Retrieve exchange rate by currency symbol and date e.g. GBP
     *
     * @param inputCurrency $inputCurrency  Currency symbol of the currency you want to exchange from.
     * @param outputCurreny $outputCurreny  Currency symbol of the currency you want to exchange to.
     * @param date          $date           Date of exchange rate  
     * @param xml           $xml            XML source that will be searched through
     * 
     * @return order    returns exchange rate with matching criteria
     */ 
    public function getExRates($inputCurrency,$outputCurreny,$date,$xml){
        $exRate= $xml->xpath("//code[text()='".$inputCurrency."']/..//rates[@date='".$date."']");
        if(empty($exRate)){
            $this->Error("Failed to find input Exchange Rate of " . $outputCurreny . " for " . $date);
        }else{

            $outputExRate = $exRate[0]->xpath(".//rate[@code='".$outputCurreny."']/@value");
            if(empty($outputExRate)){
                $this->Error("Failed to find output Exchange Rate of " . $outputCurreny);
            }
            return $outputExRate[0];
        }
    }

    /**
     * Reports error then ends program
     *
     * @param message  $message  Error to report on screen
     * 
     */ 
    public function Error($message){
       Echo "Error: " . $message;
       return Command::FAILURE;
    }

  


}