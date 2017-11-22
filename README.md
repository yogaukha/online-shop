# online-shop
Online Shop API using Lumen Framework

## Description
This Online Shop is created using Lumen Framework and microservice best practice approach

## Prerequisites
- OpenSSL PHP Extension
- PHP >= 7.0
- Mbstring Extension
- PHP PDO Extension
- MySQL DB Server

## Other Prerequisites
Before install this API, create DB use "os_" prefix to each microservice and do not forget to edit the .env file to suit your local environment.
`CREATE DATABASE os_order`

## Installation
1. clone this repository, to your local repo
`git clone https://github.com/yogaukha/online-shop.git`

2. change directory to microservice using new tab terminal
`cd online-shop/order-api/`

3. change the permission on run.sh
`chmod +x run.sh`

4. run the run.sh
- Linux & Windows `./run.sh`
- MAC `sh run.sh`
### Repeat step 2 till 4 on all microservice

## Assumption
1.	Shipment Fee.
-	I assume that the weight of product using grams, so it must divides by 1000.
-	I assume that the shipment fee is IDR 14.000, I take it from average of shipment fee from Jogja to Jakarta
2.	The discount is using nominal.
3.	Order is valid when the amount paid from customer matched with the grand total of order, and payment date is not empty, and payment proof is not empty. Else will reject (cancel) the order.	

## Endpoints API
The Ports that used is hardcode, so you won't to change it.
You can run this API with Postman Application.

### User API, act as API Gateway
1. Use Port 3132, Endpoints: ```http://localhost:3132/api/v1/user/```
2. Register
- Method: POST
- Parameter: {name}, {email}, {password}, {role}. All parameters are required.
- URI: ```register```
3. Login
- Method: POST
- Parameter: {email}, {password}
- URI: ```login```
- Return value: The one of return value is ```api_token```, you have to save it for accessing the other API 

### Product API
1. Use Port 3131, Endpoints: ```http://localhost:3131/api/v1/product/```
2. Get all products
- Method: GET
- Header: api_token, required
- Parameter: None
- URI: None
3. Get product
- Method: GET
- Header: api_token, required
- Parameter: {id}
- URI: ```1```

### Coupon API
1. Use Port 3134, Endpoints: ```http://localhost:3134/api/v1/coupon/```
2. Get all coupons
- Method: GET
- Header: api_token, required
- Parameter: None
- URI: None
3. Get coupon detail
- Method: GET
- Header: api_token, required
- Parameter: {code_name}
- URI: ```HARBOLNAS11```

### Cart API
1. Use Port 3133, Endpoints: ```http://localhost:3133/api/v1/cart/```
2. Add to cart
- Method: GET
- Header: api_token, required
- Parameter: {product_id}, {qty}
- URI: ```add-to-cart/2/1```
3. Get cart
- Method: GET
- Header: api_token, required
- Parameter: None
- URI: ```get-cart```
4. Remove from cart
- Method: GET
- Header: api_token, required
- Parameter: {product_id}
- URI: ```remove-product/1```
5. Submit coupon
- Method: POST
- Header: api_token, required
- Parameter: {code_name}, required
- URI: ```submit-coupon```
6. Remove coupon
- Method: GET
- Header: api_token, required
- Parameter: None
- URI: ```remove-coupon```
7. Delete cart
- Method: GET
- Header: api_token, required
- Parameter: None
- URI: ```delete-cart```

### Order API
1. Use Port 3135, Endpoints: ```http://localhost:3135/api/v1/order/```
2. Checkout cart
- Method: POST
- Header: api_token, required
- Parameter: {shipping_name}, {shipping_phone}, {shipping_email}, {shipping_address}, all parameters are required
- URI: ```checkout```
- Return Value: the one which is ```order_no```, you have to save it to confirm the payment later
3. Payment Confirmation
- Method: POST
- Header: api_token, required
- Parameter: {order_no}, {paid_grand_total}, {paid_date}, {payment_proof}, all parameters are required
- URI: ```payment-confirm```
4. Track Order
- Method: POST
- Header: api_token, required
- Parameter: {order_no}, required
- URI: ```track```
5. Track Shipment
- Method: POST
- Header: api_token, required
- Parameter: {order_no}, {shipping_ID}, required
- URI: ```track-shipment```
6. List all orders
- Method: GET
- Header: api_token, required
- Parameter: None
- URI: None
- Role: Admin
7. Get detail Order
- Method: GET
- Header: api_token, required
- Parameter: {id}
- URI: ```1```
- Role: Admin
8. Validate order (automatically check the order is valid or invalid based on my #Assumption above)
- Method: POST
- Header: api_token, required
- Parameter: {order_no}, required
- URI: ```validate```
- Role: Admin
9. Submit the Shipping ID to Order
- Method: POST
- Header: api_token, required
- Parameter: {order_no}, {shipping_ID}, All parameters are required
- URI: ```shipment```
- Role: Admin

## License
This project is licensed under the MIT License
