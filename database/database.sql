CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB;

CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    galon_out INT NOT NULL,
    galon_in INT NOT NULL,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_price DECIMAL(10, 2) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id)
) ENGINE=InnoDB;

CREATE OR REPLACE VIEW report_transaction_per_date AS
SELECT
    DATE(transaction_date) AS date,
    COUNT(*) AS total_transactions,
    SUM(galon_out) AS total_galon_out,
    SUM(galon_in) AS total_galon_in,
    SUM(total_price) AS total_revenue
FROM
    transactions
WHERE
    is_active = TRUE
GROUP BY
    DATE(transaction_date)
ORDER BY
    DATE(transaction_date);

DELIMITER //

CREATE PROCEDURE delete_transaction(
    IN p_id INT
)
BEGIN
    UPDATE transactions
    SET is_active = FALSE
    WHERE id = p_id;
END //

CREATE PROCEDURE save_transaction(
    IN p_id INT,
    IN p_customer_id INT,
    IN p_galon_out INT,
    IN p_galon_in INT,
    IN p_total_price DECIMAL(10, 2)
)
BEGIN
    INSERT INTO transactions (id, customer_id, galon_out, galon_in, total_price)
    VALUES (p_id, p_customer_id, p_galon_out, p_galon_in, p_total_price)
    ON DUPLICATE KEY UPDATE
        customer_id = VALUES(customer_id),
        galon_out = VALUES(galon_out),
        galon_in = VALUES(galon_in),
        total_price = VALUES(total_price),
        transaction_date = CURRENT_TIMESTAMP;
END //

CREATE PROCEDURE delete_customer(
    IN p_id INT
)
BEGIN
    UPDATE customers
    SET is_active = FALSE,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = p_id;
END //

CREATE PROCEDURE save_customer(
    IN p_id INT,
    IN p_name VARCHAR(100),
    IN p_phone_number VARCHAR(20),
    IN p_address TEXT,
    IN p_email VARCHAR(100)
)
BEGIN
    INSERT INTO customers (id, name, phone_number, address, email)
    VALUES (p_id, p_name, p_phone_number, p_address, p_email)
    ON DUPLICATE KEY UPDATE
        name = VALUES(name),
        phone_number = VALUES(phone_number),
        address = VALUES(address),
        email = VALUES(email),
        updated_at = CURRENT_TIMESTAMP;
END //

DELIMITER ;
