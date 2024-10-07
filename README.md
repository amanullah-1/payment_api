
# **Submission Document: Payment Processing API & CLI Command**

## **Project Overview**
This task implements an API endpoint and CLI command for processing payments via two external systems: **Shift4** and **ACI**. The solution is built using Symfony 6.4 and PHP 8 and follows best practices, including error handling and unit testing.

---

## **API Documentation**

### **Endpoint: `/app/example/{system}`**

- **Description**: Processes a payment via the specified external system (Shift4 or ACI).
- **Method**: `POST`
- **URL**: `/app/example/{system}`

### **Parameters**:
| Parameter     | Type   | Required | Description                           |
|---------------|--------|----------|---------------------------------------|
| `system`      | String | Yes      | The payment system, either `aci` or `shift4`. |
| `amount`      | Float  | Yes      | The amount to be charged.             |
| `currency`    | String | Yes      | The currency for the transaction (e.g., USD). |
| `card_number` | String | Yes      | The credit card number.               |
| `exp_year`    | String | Yes      | The card's expiration year.           |
| `exp_month`   | String | Yes      | The card's expiration month.          |
| `cvv`         | String | Yes      | The card's CVV code.                  |
| `paymeny_brand`         | String | No      | VISA or Master Card or Else.                  |
| `payment_type`         | String | No      | DB -Debit Card, or CC - Credit Card                  |

### **Example Request**:
```bash
curl -X POST http://127.0.0.1:8000/app/example/shift4 \
  -d "amount=100" \
  -d "currency=USD" \
  -d "card_number=4242424242424242" \
  -d "exp_year=2024" \
  -d "exp_month=10" \
  -d "cvv=123"
```

### **Example Response**:
```json

{
    "transaction_id": "char_VEdIxLLHlaidmzwP0VWoQUEQ",
    "created_at": "2024-10-07 17:32:22",
    "amount": 100,
    "currency": "EUR",
    "card_bin": "N/A",
    "status": "successful"
}
```

### **Error Handling**:
- **401 Unauthorized**: Returned if the API key or token is invalid.
- **400 Bad Request**: Returned if any required parameter is missing or invalid.
- **500 Internal Server Error**: Returned if the external API fails or if there’s an issue with the service.

---

## **CLI Command Documentation**

### **Command: `app:example`**

- **Description**: Processes a payment via the specified system using CLI arguments.
- **Usage**: 
  ```bash
  php bin/console app:example {system} {amount} {currency} {card_number} {exp_year} {exp_month} {cvv}
  ```

### **Parameters**:
| Argument      | Type   | Required | Description                           |
|---------------|--------|----------|---------------------------------------|
| `system`      | String | Yes      | The payment system (`aci` or `shift4`). |
| `amount`      | Float  | Yes      | The amount to be charged.             |
| `currency`    | String | Yes      | The currency for the transaction (e.g., USD). |
| `card_number` | String | Yes      | The credit card number.               |
| `exp_year`    | String | Yes      | The card's expiration year.           |
| `exp_month`   | String | Yes      | The card's expiration month.          |
| `cvv`         | String | Yes      | The card's CVV code.                  |
| `paymeny_brand`         | String | No      | VISA or Master Card or Else.                  |
| `payment_type`         | String | No      | DB -Debit Card, or CC - Credit Card                  |

### **Example**:
```bash
php bin/console app:example shift4 100 USD 4242424242424242 2024 10 123
```

### **Expected Output**:
```json
{
  "transaction_id": "txn_12345",
  "created_at": "2024-10-05 14:22:05",
  "amount": 100,
  "currency": "USD",
  "card_bin": "424242"
}
```

### **Error Handling**:
- Errors will be logged to the console with appropriate messages, such as invalid parameters or API call failures.

---

## **Unit Test Documentation**

### **Running Tests**:
This project includes unit tests for the `PaymentService`, which handles interactions with external payment systems.

1. **Ensure Dependencies Are Installed**:
   ```bash
   composer install
   ```

2. **Run the Tests**:
   To run the PHPUnit tests, use the following command:
   ```bash
   php bin/phpunit
   ```

### **Unit Tests**:
- The `PaymentServiceTest` file is located in `tests/Service/PaymentServiceTest.php` and contains tests for both Shift4 and ACI payment processing.
  
### **Test Example**:
- **Test Case**: `testProcessShift4Payment` verifies that the Shift4 payment processing returns the correct transaction information when given valid inputs.

### **Mocking the API Response**:
In the test, the API response from Shift4 or ACI is mocked to simulate different scenarios like successful payments and failures.

### **Test Example Code**:
```php
// tests/Service/PaymentServiceTest.php
public function testProcessShift4Payment()
{
    $response = $this->createMock(ResponseInterface::class);
    $response->method('toArray')->willReturn([
        'transaction_id' => 'txn_12345',
        'amount' => 100,
        'currency' => 'USD',
        'card' => ['number' => '4242424242424242'],
    ]);

    $this->client->method('request')->willReturn($response);

    $result = $this->paymentService->processPayment(
        'shift4', 100, 'USD', '4242424242424242', '2024', '10', '123'
    );

    $this->assertEquals('txn_12345', $result['transaction_id']);
    $this->assertEquals(100, $result['amount']);
    $this->assertEquals('USD', $result['currency']);
    $this->assertEquals('424242', $result['card_bin']);
}
```

---

## **Docker Setup and Documentation**

This project includes a Docker setup to simplify the environment configuration. The `Dockerfile` and `docker-compose.yml` files enable you to run the application in containers using PHP-FPM and Nginx.

### **Docker Setup Instructions**

1. **Build and Run the Docker Containers**:
   To set up the environment using Docker, run the following commands:
   ```bash
   docker-compose up --build
   ```

2. **Access the Application**:
   Once the Docker containers are running, we can access the API at:
   ```
   http://localhost
   ```

3. **Run Symfony Commands Inside Docker**:
   If you need to run Symfony commands (like the CLI payment command), you can execute them within the PHP container:
   ```bash
   docker-compose exec php php bin/console app:example shift4 100 USD 4242424242424242 2024 10 123
   ```

### **Docker Configuration**:

- **Dockerfile**: Defines the PHP environment with necessary extensions.
  
- **docker-compose.yml**: Sets up services for PHP and Nginx, ensuring your Symfony project is properly served.

### **Dockerfile Example**:
```dockerfile
FROM php:8.1-fpm

WORKDIR /app

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev

RUN docker-php-ext-install pdo pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install

CMD ["php-fpm"]
```

### **docker-compose.yml Example**:
```yaml
version: '3.8'

services:
  php:
    build: .
    container_name: symfony_php
    volumes:
      - ./:/app
    ports:
      - "9000:9000"
    networks:
      - symfony_net

  nginx:
    image: nginx:alpine
    container_name: symfony_nginx
    ports:
      - "80:80"
    volumes:
      - ./:/app
      - ./docker/nginx/nginx.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - php
    networks:
      - symfony_net

networks:
  symfony_net:
    driver: bridge
```

---

## **Edge Cases and Error Handling**

### **API Error Handling**:
The system handles various edge cases:
- Invalid or missing input parameters (returns a `400 Bad Request` response).
- API errors, such as failed calls to Shift4 or ACI, are logged, and appropriate error messages are returned.
- Unauthorized requests to external APIs (e.g., missing or invalid API keys) return `401 Unauthorized`.

### **CLI Error Handling**:
Errors encountered during the CLI execution are printed to the console, including:
- Invalid system names (e.g., anything other than `aci` or `shift4`).
- Invalid card details, expiration dates, etc.


