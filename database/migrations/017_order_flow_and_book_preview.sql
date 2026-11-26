ALTER TABLE orders
    MODIFY status ENUM('pending', 'confirmed', 'shipping', 'delivered', 'completed', 'cancel_requested', 'return_requested', 'return_shipping', 'returned', 'return_rejected', 'cancelled') NOT NULL DEFAULT 'pending',
    ADD COLUMN cancel_reason TEXT NULL AFTER shipping_address,
    ADD COLUMN cancel_requested_at DATETIME NULL AFTER cancel_reason,
    ADD COLUMN return_reason TEXT NULL AFTER cancel_requested_at,
    ADD COLUMN return_image VARCHAR(255) NULL AFTER return_reason,
    ADD COLUMN return_requested_at DATETIME NULL AFTER return_image,
    ADD COLUMN return_review_reason TEXT NULL AFTER return_requested_at,
    ADD COLUMN return_reviewed_at DATETIME NULL AFTER return_review_reason;

ALTER TABLE order_status_history
    MODIFY from_status ENUM('pending', 'confirmed', 'shipping', 'delivered', 'completed', 'cancel_requested', 'return_requested', 'return_shipping', 'returned', 'return_rejected', 'cancelled') NULL,
    MODIFY to_status ENUM('pending', 'confirmed', 'shipping', 'delivered', 'completed', 'cancel_requested', 'return_requested', 'return_shipping', 'returned', 'return_rejected', 'cancelled') NOT NULL;

ALTER TABLE books
    ADD COLUMN preview_content TEXT NULL AFTER description;
