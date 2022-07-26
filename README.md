## Order Currency Conversion Utility

This sample demonstrates a Symfony app running in the console to provide currency conversions for orders in an XML format.  

### Quickstart Commands
```
symfony console app:order-rate {order id} {output currency symbol}
```

For example the following converts the currency of order (id = 1) into EUR
```
symfony console app:order-rate 1 EUR
```

output
```xml
<order>
  <id>1</id>
  <currency>EUR</currency>
  <date>01/01/2022</date>
  <products>
    <product title="Rimmel Lasting Finish Lipstick 4g" price="5.49"/>
    <product title="Sebamed Shampoo 200ml" price="5.49"/>
  </products>
  <total>10.98</total>
</order>
```



This second example converts the currency of order (id = 2) into GBP
```
symfony console app:order-rate 2 GBP
```
output
```xml
	<order>
		<id>2</id>
		<currency>GBP</currency>
		<date>02/01/2022</date>
		<products>
			<product title="GHD Hair Straighteners" price="80"/>
			<product title="Redken Shampure Shampoo" price="16"/>
		</products>
		<total>95.99</total>
	</order>
```



