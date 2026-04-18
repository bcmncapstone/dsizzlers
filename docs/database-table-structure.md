# Database Table Structure

This document describes the current database tables based on the Laravel migrations inside `database/migrations`.

## TABLE 1. ADMINS

This table contains the information of the franchisor or admin.

| Field Name | Type | Description |
| --- | --- | --- |
| `admin_id` | `bigint` | A unique identifier for the admin and the primary key of the table. |
| `admin_fname` | `varchar(30)` | First name of the admin. |
| `admin_lname` | `varchar(30)` | Last name of the admin. |
| `admin_contactNo` | `varchar(11)` | Contact number of the admin. |
| `admin_email` | `varchar(40)` | Email address of the admin. This value is unique. |
| `admin_username` | `varchar(30)` | Username of the admin. This value is unique. |
| `admin_pass` | `varchar(255)` | Hashed password of the admin. |
| `reset_password_token` | `text` nullable | Token used for password reset requests. |
| `reset_password_expires_at` | `timestamp` nullable | Expiration date and time of the password reset token. |
| `admin_status` | `enum('Active','Inactive')` | Status of the admin account. |
| `created_at` | `timestamp` | Date and time when the record was created. |
| `updated_at` | `timestamp` | Date and time when the record was last updated. |

## TABLE 2. ADMIN STAFF

This table contains the personal information of the staff working under the admin.

| Field Name | Type | Description |
| --- | --- | --- |
| `astaff_id` | `bigint` | A unique identifier for the staff member and the primary key of the table. |
| `admin_id` | `bigint` | Foreign key linking to `admin_id` in the `admins` table. |
| `astaff_fname` | `varchar(30)` | First name of the staff member. |
| `astaff_lname` | `varchar(30)` | Last name of the staff member. |
| `astaff_contactNo` | `varchar(11)` | Contact number of the staff member. |
| `astaff_email` | `varchar(120)` nullable | Email address of the staff member. This value is unique when present. |
| `astaff_username` | `varchar(30)` | Username of the staff member. This value is unique. |
| `astaff_pass` | `varchar(255)` | Hashed password of the staff member. |
| `reset_password_token` | `text` nullable | Token used for password reset requests. |
| `reset_password_expires_at` | `timestamp` nullable | Expiration date and time of the password reset token. |
| `astaff_status` | `enum('Active','Inactive')` | Status of the staff account. |
| `created_at` | `timestamp` | Date and time when the record was created. |
| `updated_at` | `timestamp` | Date and time when the record was last updated. |

## TABLE 3. FRANCHISEES

This table contains the information of the franchisees.

| Field Name | Type | Description |
| --- | --- | --- |
| `franchisee_id` | `bigint` | A unique identifier for the franchisee and the primary key of the table. |
| `admin_id` | `bigint` | Foreign key linking to `admin_id` in the `admins` table. |
| `franchisee_name` | `varchar(50)` | Name of the franchisee. |
| `franchisee_contactNo` | `varchar(11)` | Contact number of the franchisee. |
| `franchisee_email` | `varchar(40)` | Email address of the franchisee. This value is unique. |
| `franchisee_username` | `varchar(30)` | Username of the franchisee. This value is unique. |
| `franchisee_pass` | `varchar(255)` | Hashed password of the franchisee. |
| `reset_password_token` | `text` nullable | Token used for password reset requests. |
| `reset_password_expires_at` | `timestamp` nullable | Expiration date and time of the password reset token. |
| `franchisee_address` | `varchar(255)` | Physical address of the franchisee. |
| `franchisee_status` | `enum('Active','Inactive')` | Status of the franchisee account. |
| `created_at` | `timestamp` | Date and time when the record was created. |
| `updated_at` | `timestamp` | Date and time when the record was last updated. |

## TABLE 4. FRANCHISEE STAFF

This table contains the personal information of the staff working under the franchisee.

| Field Name | Type | Description |
| --- | --- | --- |
| `fstaff_id` | `bigint` | A unique identifier for the staff member and the primary key of the table. |
| `franchisee_id` | `bigint` | Foreign key linking to `franchisee_id` in the `franchisees` table. |
| `fstaff_fname` | `varchar(50)` | First name of the staff member. |
| `fstaff_lname` | `varchar(50)` | Last name of the staff member. |
| `fstaff_contactNo` | `varchar(13)` | Contact number of the staff member. |
| `fstaff_email` | `varchar(120)` nullable | Email address of the staff member. This value is unique when present. |
| `fstaff_username` | `varchar(50)` | Username of the staff member. This value is unique. |
| `fstaff_pass` | `varchar(255)` | Hashed password of the staff member. |
| `reset_password_token` | `text` nullable | Token used for password reset requests. |
| `reset_password_expires_at` | `timestamp` nullable | Expiration date and time of the password reset token. |
| `fstaff_status` | `enum('Active','Inactive')` | Status of the staff account. |
| `created_at` | `timestamp` | Date and time when the record was created. |
| `updated_at` | `timestamp` | Date and time when the record was last updated. |

## TABLE 5. ITEMS

This table contains the master list of products or inventory items.

| Field Name | Type | Description |
| --- | --- | --- |
| `item_id` | `bigint` | A unique identifier for the item and the primary key of the table. |
| `item_name` | `varchar(50)` | Name of the item. |
| `item_image` | `text` nullable | Path or stored value of the item image. |
| `item_description` | `text` nullable | Description of the item. |
| `price` | `float` | Current price of the item. |
| `stock_quantity` | `int` | Current stock quantity of the item in the main inventory. |
| `item_category` | `varchar(30)` | Category of the item. |
| `created_at` | `timestamp` | Date and time when the record was created. |
| `updated_at` | `timestamp` | Date and time when the record was last updated. |

## TABLE 6. ORDERS

This table contains the main order information.

| Field Name | Type | Description |
| --- | --- | --- |
| `order_id` | `bigint` | A unique identifier for the order and the primary key of the table. |
| `franchisee_id` | `bigint` nullable | Foreign key linking to `franchisee_id` in the `franchisees` table. |
| `fstaff_id` | `bigint` nullable | Foreign key linking to `fstaff_id` in the `franchisee_staff` table. |
| `order_date` | `datetime` | Date and time when the order was placed. |
| `order_status` | `enum('Pending','Preparing','Shipped','Delivered','Cancelled')` | Current processing status of the order. |
| `total_amount` | `decimal(8,2)` | Total amount of the order. |
| `order_notes` | `text` nullable | Additional notes or remarks for the order. |
| `name` | `varchar(255)` nullable | Name used for the order recipient or customer record. |
| `contact` | `varchar(50)` nullable | Contact number or contact detail for the order. |
| `address` | `varchar(500)` nullable | Delivery or customer address for the order. |
| `payment_receipt` | `varchar(255)` nullable | Stored receipt file path or reference. |
| `payment_status` | `varchar(50)` | Payment status of the order. |
| `delivery_status` | `varchar(50)` | Delivery status of the order. |
| `created_at` | `timestamp` | Date and time when the record was created. |
| `updated_at` | `timestamp` | Date and time when the record was last updated. |

## TABLE 7. ORDER DETAILS

This table contains the line items included in each order.

| Field Name | Type | Description |
| --- | --- | --- |
| `order_detail_id` | `bigint` | A unique identifier for the order detail and the primary key of the table. |
| `order_id` | `bigint` | Foreign key linking to `order_id` in the `orders` table. |
| `item_id` | `bigint` | Foreign key linking to `item_id` in the `items` table. |
| `quantity` | `int` | Quantity of the item included in the order. |
| `subtotal` | `decimal(8,2)` | Subtotal amount for the line item. |
| `price` | `float` | Recorded item price at the time of the order. |
| `created_at` | `timestamp` | Date and time when the record was created. |
| `updated_at` | `timestamp` | Date and time when the record was last updated. |

## TABLE 8. PAYMENTS

This table contains payment records related to orders.

| Field Name | Type | Description |
| --- | --- | --- |
| `payment_id` | `bigint` | A unique identifier for the payment and the primary key of the table. |
| `order_id` | `bigint` | Foreign key linking to `order_id` in the `orders` table. |
| `item_id` | `bigint` nullable | Foreign key linking to `item_id` in the `items` table. |
| `amount_paid` | `float` | Amount paid in the transaction. |
| `date` | `date` | Date when the payment was made. |
| `status` | `varchar(50)` | Status of the payment record. |
| `receipt` | `varchar(100)` nullable | Receipt file name or receipt reference. |
| `created_at` | `timestamp` | Date and time when the record was created. |
| `updated_at` | `timestamp` | Date and time when the record was last updated. |

## TABLE 9. PRICE USED

This table stores the item price history used by the system.

| Field Name | Type | Description |
| --- | --- | --- |
| `price_used_id` | `bigint` | A unique identifier for the price history record and the primary key of the table. |
| `item_id` | `bigint` | Foreign key linking to `item_id` in the `items` table. |
| `used_price` | `decimal(10,2)` | Recorded price used for the item. |
| `used_date` | `timestamp` | Date and time when the price was used or recorded. |
| `created_at` | `timestamp` | Date and time when the record was created. |
| `updated_at` | `timestamp` | Date and time when the record was last updated. |

## TABLE 10. STOCK IN

This table records stock replenishment or delivery into inventory.

| Field Name | Type | Description |
| --- | --- | --- |
| `stock_in_id` | `bigint` | A unique identifier for the stock-in record and the primary key of the table. |
| `item_id` | `bigint` | Foreign key linking to `item_id` in the `items` table. |
| `quantity_received` | `int` | Quantity of stock received. |
| `received_date` | `datetime` | Date and time when the stock was received. |
| `supplier_name` | `varchar(50)` nullable | Name of the supplier of the stock. |
| `restocked_by` | `bigint` | ID of the user who performed the restocking. |
| `created_at` | `timestamp` | Date and time when the record was created. |
| `updated_at` | `timestamp` | Date and time when the record was last updated. |

## TABLE 11. FRANCHISEE STOCK

This table tracks the current stock quantity of each item assigned to a franchisee.

| Field Name | Type | Description |
| --- | --- | --- |
| `stock_id` | `bigint` | A unique identifier for the stock record and the primary key of the table. |
| `franchisee_id` | `bigint` | Foreign key linking to `franchisee_id` in the `franchisees` table. |
| `item_id` | `bigint` | Foreign key linking to `item_id` in the `items` table. |
| `current_quantity` | `int` | Current quantity of the item available to the franchisee. |
| `minimum_quantity` | `int` | Minimum quantity threshold used for stock alerts. |
| `created_at` | `timestamp` | Date and time when the record was created. |
| `updated_at` | `timestamp` | Date and time when the record was last updated. |

## TABLE 12. STOCK TRANSACTIONS

This table records the movement history of stock for each franchisee and item.

| Field Name | Type | Description |
| --- | --- | --- |
| `transaction_id` | `bigint` | A unique identifier for the stock transaction and the primary key of the table. |
| `franchisee_id` | `bigint` | Foreign key linking to `franchisee_id` in the `franchisees` table. |
| `item_id` | `bigint` | Foreign key linking to `item_id` in the `items` table. |
| `transaction_type` | `enum('in','out','adjustment')` | Type of stock transaction. |
| `quantity` | `int` | Quantity moved in the transaction. |
| `balance_after` | `int` | Remaining balance after the transaction. |
| `reference_type` | `varchar(50)` nullable | Reference source of the transaction, such as order or manual adjustment. |
| `reference_id` | `bigint` nullable | Related record ID for the transaction reference. |
| `notes` | `text` nullable | Additional notes for the transaction. |
| `performed_by_type` | `varchar(50)` nullable | Type of user who performed the transaction. |
| `performed_by_id` | `bigint` nullable | ID of the user who performed the transaction. |
| `created_at` | `timestamp` | Date and time when the record was created. |
| `updated_at` | `timestamp` | Date and time when the record was last updated. |

## TABLE 13. CART ITEMS

This table stores items added to a cart before checkout.

| Field Name | Type | Description |
| --- | --- | --- |
| `cart_item_id` | `bigint` | A unique identifier for the cart item and the primary key of the table. |
| `franchisee_id` | `bigint` nullable | Foreign key linking to `franchisee_id` in the `franchisees` table. |
| `fstaff_id` | `bigint` nullable | Foreign key linking to `fstaff_id` in the `franchisee_staff` table. |
| `item_id` | `bigint` | Foreign key linking to `item_id` in the `items` table. |
| `quantity` | `unsigned int` | Quantity of the item added to the cart. |
| `created_at` | `timestamp` | Date and time when the record was created. |
| `updated_at` | `timestamp` | Date and time when the record was last updated. |

## TABLE 14. BRANCHES

This table stores branch records and contract information.

| Field Name | Type | Description |
| --- | --- | --- |
| `branch_id` | `bigint` | A unique identifier for the branch and the primary key of the table. |
| `location` | `varchar(255)` | Branch location. |
| `first_name` | `varchar(255)` | First name of the branch contact person. |
| `last_name` | `varchar(255)` | Last name of the branch contact person. |
| `email` | `varchar(255)` | Email address of the branch contact. |
| `contact_number` | `varchar(255)` | Contact number of the branch. |
| `contract_file` | `varchar(255)` nullable | Uploaded contract file path. |
| `contract_expiration` | `date` | Expiration date of the branch contract. |
| `branch_status` | `boolean` | Indicates whether the branch is active. |
| `archived` | `boolean` | Indicates whether the branch is archived. |
| `created_at` | `timestamp` | Date and time when the record was created. |
| `updated_at` | `timestamp` | Date and time when the record was last updated. |

## TABLE 15. CONVERSATIONS

This table stores chat conversation threads between admins and franchisees.

| Field Name | Type | Description |
| --- | --- | --- |
| `id` | `bigint` | A unique identifier for the conversation and the primary key of the table. |
| `admin_id` | `bigint` | ID of the admin participating in the conversation. |
| `franchisee_id` | `bigint` | ID of the franchisee participating in the conversation. |
| `created_at` | `timestamp` | Date and time when the record was created. |
| `updated_at` | `timestamp` | Date and time when the record was last updated. |

## TABLE 16. MESSAGES

This table stores the individual messages inside each conversation.

| Field Name | Type | Description |
| --- | --- | --- |
| `id` | `bigint` | A unique identifier for the message and the primary key of the table. |
| `conversation_id` | `bigint` | Foreign key linking to the `conversations` table. |
| `sender_id` | `bigint` | ID of the user who sent the message. |
| `sender_type` | `varchar(255)` | Type of sender, such as admin or franchisee. |
| `message_text` | `text` nullable | Text content of the message. |
| `file_path` | `varchar(255)` nullable | Stored file path of the attached file. |
| `file_name` | `varchar(255)` nullable | Original or display name of the attached file. |
| `file_type` | `varchar(255)` nullable | File type of the attachment. |
| `created_at` | `timestamp` | Date and time when the record was created. |
| `updated_at` | `timestamp` | Date and time when the record was last updated. |

## TABLE 17. DIGITAL MARKETING UPLOADS

This table stores marketing images uploaded to the system.

| Field Name | Type | Description |
| --- | --- | --- |
| `id` | `bigint` | A unique identifier for the upload and the primary key of the table. |
| `uploaded_by` | `bigint` | ID of the user who uploaded the marketing file. |
| `image_path` | `varchar(255)` | Stored image path of the uploaded file. |
| `description` | `varchar(255)` nullable | Optional description of the uploaded image. |
| `created_at` | `timestamp` | Date and time when the record was created. |
| `updated_at` | `timestamp` | Date and time when the record was last updated. |

## Notes

- The table descriptions above reflect the current schema, not the older sample format.
- Several password fields in the current application use `varchar(255)` because they store hashed passwords.
- Some fields such as `restocked_by`, `uploaded_by`, `sender_id`, and the IDs in `conversations` are stored as numeric references without strict database foreign key constraints.
- If you need a shortened version for a thesis or capstone document, you may keep only the core tables: `admins`, `admin_staff`, `franchisees`, `franchisee_staff`, `items`, `orders`, `order_details`, `payments`, `price_used`, and `stock_in`.
