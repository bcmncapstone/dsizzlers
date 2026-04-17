# Database ERD

This document summarizes the current database structure based on the Laravel migrations in `database/migrations`.

## Main ERD

```mermaid
erDiagram
    ADMINS {
        bigint admin_id PK
        string admin_fname
        string admin_lname
        string admin_contactNo
        string admin_email
        string admin_username
        string admin_pass
        string admin_status
        timestamp created_at
        timestamp updated_at
    }

    ADMIN_STAFF {
        bigint astaff_id PK
        bigint admin_id FK
        string astaff_fname
        string astaff_lname
        string astaff_contactNo
        string astaff_email
        string astaff_username
        string astaff_pass
        string astaff_status
        timestamp created_at
        timestamp updated_at
    }

    FRANCHISEES {
        bigint franchisee_id PK
        bigint admin_id FK
        string franchisee_name
        string franchisee_contactNo
        string franchisee_email
        string franchisee_username
        string franchisee_pass
        string franchisee_address
        string franchisee_status
        timestamp created_at
        timestamp updated_at
    }

    FRANCHISEE_STAFF {
        bigint fstaff_id PK
        bigint franchisee_id FK
        string fstaff_fname
        string fstaff_lname
        string fstaff_contactNo
        string fstaff_email
        string fstaff_username
        string fstaff_pass
        string fstaff_status
        timestamp created_at
        timestamp updated_at
    }

    ITEMS {
        bigint item_id PK
        string item_name
        text item_description
        float price
        int stock_quantity
        string item_category
        text item_image
        timestamp created_at
        timestamp updated_at
    }

    PRICE_USED {
        bigint price_used_id PK
        bigint item_id FK
        decimal used_price
        timestamp used_date
        timestamp created_at
        timestamp updated_at
    }

    STOCK_IN {
        bigint stock_in_id PK
        bigint item_id FK
        int quantity_received
        datetime received_date
        string supplier_name
        bigint restocked_by
        timestamp created_at
        timestamp updated_at
    }

    FRANCHISEE_STOCK {
        bigint stock_id PK
        bigint franchisee_id FK
        bigint item_id FK
        int current_quantity
        int minimum_quantity
        timestamp created_at
        timestamp updated_at
    }

    STOCK_TRANSACTIONS {
        bigint transaction_id PK
        bigint franchisee_id FK
        bigint item_id FK
        string transaction_type
        int quantity
        int balance_after
        string reference_type
        bigint reference_id
        text notes
        string performed_by_type
        bigint performed_by_id
        timestamp created_at
        timestamp updated_at
    }

    ORDERS {
        bigint order_id PK
        bigint franchisee_id FK
        bigint fstaff_id FK
        datetime order_date
        string order_status
        decimal total_amount
        text order_notes
        string name
        string contact
        string address
        string payment_receipt
        string payment_status
        string delivery_status
        timestamp created_at
        timestamp updated_at
    }

    ORDER_DETAILS {
        bigint order_detail_id PK
        bigint order_id FK
        bigint item_id FK
        int quantity
        decimal subtotal
        float price
        timestamp created_at
        timestamp updated_at
    }

    PAYMENTS {
        bigint payment_id PK
        bigint order_id FK
        bigint item_id FK
        float amount_paid
        date date
        string status
        string receipt
        timestamp created_at
        timestamp updated_at
    }

    CART_ITEMS {
        bigint cart_item_id PK
        bigint franchisee_id FK
        bigint fstaff_id FK
        bigint item_id FK
        int quantity
        timestamp created_at
        timestamp updated_at
    }

    BRANCHES {
        bigint branch_id PK
        string location
        string first_name
        string last_name
        string email
        string contact_number
        string contract_file
        date contract_expiration
        boolean branch_status
        boolean archived
        timestamp created_at
        timestamp updated_at
    }

    CONVERSATIONS {
        bigint id PK
        bigint admin_id
        bigint franchisee_id
        timestamp created_at
        timestamp updated_at
    }

    MESSAGES {
        bigint id PK
        bigint conversation_id FK
        bigint sender_id
        string sender_type
        text message_text
        string file_path
        string file_name
        string file_type
        timestamp created_at
        timestamp updated_at
    }

    DIGITAL_MARKETING_UPLOADS {
        bigint id PK
        bigint uploaded_by
        string image_path
        string description
        timestamp created_at
        timestamp updated_at
    }

    ADMINS ||--o{ ADMIN_STAFF : manages
    ADMINS ||--o{ FRANCHISEES : owns
    FRANCHISEES ||--o{ FRANCHISEE_STAFF : has

    FRANCHISEES ||--o{ ORDERS : places
    FRANCHISEE_STAFF ||--o{ ORDERS : creates
    ORDERS ||--|{ ORDER_DETAILS : contains
    ITEMS ||--o{ ORDER_DETAILS : appears_in
    ORDERS ||--o{ PAYMENTS : receives
    ITEMS ||--o{ PAYMENTS : linked_item

    ITEMS ||--o{ PRICE_USED : price_history
    ITEMS ||--o{ STOCK_IN : restocked
    FRANCHISEES ||--o{ FRANCHISEE_STOCK : stores
    ITEMS ||--o{ FRANCHISEE_STOCK : tracked_as
    FRANCHISEES ||--o{ STOCK_TRANSACTIONS : logs
    ITEMS ||--o{ STOCK_TRANSACTIONS : affects

    FRANCHISEES ||--o{ CART_ITEMS : owns_cart
    FRANCHISEE_STAFF ||--o{ CART_ITEMS : owns_cart
    ITEMS ||--o{ CART_ITEMS : added_to_cart

    CONVERSATIONS ||--o{ MESSAGES : contains
```

## Explanation of the Design

### 1. User and account management

- `admins` is the top-level account table.
- `admin_staff` stores staff members working under a specific admin.
- `franchisees` represents each franchise owner or client account and each franchisee belongs to one admin.
- `franchisee_staff` stores staff accounts under a franchisee.

This creates a hierarchy:

`Admin -> Admin Staff`

`Admin -> Franchisees -> Franchisee Staff`

### 2. Product and inventory management

- `items` is the master product table.
- `price_used` keeps a history of item prices over time.
- `stock_in` records replenishment events for items.
- `franchisee_stock` tracks how much of each item is currently assigned to a franchisee.
- `stock_transactions` provides a movement log for stock changes such as stock coming in, stock going out, or manual adjustments.

In short, `items` is the center of inventory, while the other tables describe price history, replenishment, per-franchise stock levels, and audit history.

### 3. Ordering and sales flow

- `orders` is the header table for each order.
- One order can belong directly to a `franchisee` or be created by a `franchisee_staff` account.
- `order_details` stores the line items of the order.
- `payments` stores payment records tied to the order, with an optional link to a specific item.
- `cart_items` acts as a temporary shopping cart before an order is finalized.

This is the transaction flow:

`Cart Items -> Orders -> Order Details -> Payments`

### 4. Operational extensions

- `branches` stores branch information and contract status.
- `conversations` stores chat threads between admins and franchisees.
- `messages` stores individual messages inside a conversation.
- `digital_marketing_uploads` stores uploaded media used for digital marketing content.

These tables support operations outside the core order and inventory flow.

## Relationship Summary

- One `admin` can have many `admin_staff`.
- One `admin` can have many `franchisees`.
- One `franchisee` can have many `franchisee_staff`.
- One `franchisee` can have many `orders`.
- One `franchisee_staff` can have many `orders`.
- One `order` can have many `order_details`.
- One `item` can appear in many `order_details`.
- One `order` can have many `payments`.
- One `item` can have many `price_used` records.
- One `item` can have many `stock_in` records.
- One `franchisee` can have many `franchisee_stock` records.
- One `item` can have many `franchisee_stock` records.
- One `franchisee` can have many `stock_transactions`.
- One `item` can have many `stock_transactions`.
- One `conversation` can have many `messages`.

## Important Notes About the Current Schema

- `conversations.admin_id` and `conversations.franchisee_id` are stored as IDs, but the migration does not define foreign key constraints for them.
- `digital_marketing_uploads.uploaded_by` is stored as a numeric ID, but there is no foreign key constraint showing whether it belongs to an admin, staff, or another user type.
- `stock_in.restocked_by` is also stored as a numeric ID without a strict foreign key, which suggests a polymorphic or application-level reference.
- `messages.sender_id` and `messages.sender_type` form a polymorphic-style relationship, but it is not enforced by database foreign keys.
- `orders` contains both `order_status` and the later-added `payment_status` and `delivery_status`, so status handling is split across multiple columns.
- `payments.item_id` is nullable, which means some payments are recorded at the order level rather than for one specific item.

## Suggested Presentation Script

If you need to explain this ERD in class or documentation, you can use this short version:

"The database is centered on four major modules: user management, inventory, ordering, and operations. Admins manage franchisees and admin staff. Franchisees can also have their own staff. Items are the core inventory entity, connected to price history, stock-in records, franchise stock, and stock transaction logs. The sales process starts from cart items, then moves into orders, order details, and payments. Additional tables such as branches, conversations, messages, and digital marketing uploads support the wider business workflow."
