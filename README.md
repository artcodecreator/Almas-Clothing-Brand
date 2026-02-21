# 🛍️ Almas-Clothing-Brand

**Almas Clothing Brand** is a responsive, web-based e-commerce platform designed to provide a complete online shopping experience for apparel and fashion accessories. The system supports both **customers** and **administrators**, focusing on usability, security, and scalability, with special consideration for the Pakistani market (PKR currency and Cash on Delivery).



## 📌 1. Project Overview

### 1.1 Scope of the Project

#### Web-Based Platform
This project is a **responsive e-commerce website** developed for the **Almas Clothing Brand**, enabling customers to browse, purchase, and track clothing products online.

##### Product Categories
- **Men’s Clothing**
  - Shirts
  - Trousers
  - Traditional wear
- **Women’s Clothing**
  - Dresses
  - Abayas
  - Casual wear
- **Accessories**
  - Scarves
  - Belts
  - Other fashion items



## 👤 2. User Features

- User registration, login, and secure authentication
- Profile management (personal details & order history)
- Product browsing by categories and collections
- Advanced product filtering:
  - Size
  - Price range
  - Availability
- Shopping cart management:
  - Add products
  - Update quantities
  - Remove items
- Checkout using **Cash on Delivery (COD)**
- Order tracking with status updates:
  - Order Confirmed
  - Shipped
  - Delivered
- Product reviews and ratings (only for purchased items)
- Notifications via UI and email



## 🛠️ 3. Admin Features

- Secure admin authentication
- Centralized admin dashboard
- Product management:
  - Add, edit, delete products
  - Manage categories, sizes, and stock levels
- Order management:
  - View all orders
  - Update order statuses
  - Assign couriers and tracking numbers
- User management:
  - View and manage registered users
- Coupon & discount management
- Review moderation
- Sales and revenue analytics



## 🚫 4. Out-of-Scope Features

The following features are **not included** in the current phase:

- 📱 **Mobile Application Development**  
  (Only a web-based system is implemented)

- 💳 **Online Payment Gateways**  
  (Only **Cash on Delivery – COD** is supported)



## ⚙️ 5. Functional & Non-Functional Requirements

### 5.1 Functional Requirements

#### 🧑 User Module — Functional Requirements

| Feature | Description |
|------|-----------|
| Registration / Login | Users can create accounts and log in securely |
| Product Browsing | Browse products by category with prices in PKR |
| Shopping Cart | Add/remove items, update quantities, view subtotal |
| Checkout | Secure checkout with Pakistan-standard address validation |
| Order Tracking | Real-time order status with courier links |
| Profile Management | Update personal info and view order history |



#### 🧑‍💼 Admin Module — Functional Requirements

| Feature | Description |
|------|-----------|
| Dashboard | View recent orders, users, and sales summaries |
| Product Management | Add, edit, delete products and manage stock |
| Order Management | Update order status and assign couriers |
| Coupon Management | Create and manage discount codes |
| Financial Analytics | Revenue reports, top products, performance stats |
| User Management | View, update, or deactivate user accounts |



#### ⚙️ System Module — Functional Requirements

| Feature | Description |
|------|-----------|
| Search | Keyword-based product search |
| Notifications | Order and status updates via UI/email |
| Currency Handling | Enforced PKR currency formatting |



### 5.2 Non-Functional Requirements

| Category | Description |
|------|-----------|
| Performance | Page load time within 2–3 seconds |
| Security | bcrypt password hashing, SQL injection protection |
| Reliability | 99.9% system uptime |
| Scalability | Supports increased users and new product lines |
| Usability | Fully responsive using Bootstrap 5 |
| Maintainability | Modular code structure (header, footer, DB config) |

---
## 📊 6. Use Case Diagram (Textual Description)

### Actors
- **User**
- **Admin**



### 👤 User Use Cases

| Use Case | Description |
|-------|-------------|
| Register | Create a new user account |
| Login | Authenticate using email/username and password |
| View Profile | Manage personal profile information |
| Browse Products | View products by category |
| View Product Details | View product details and PKR price |
| Search Products | Search products using keywords |
| Filter Products | Filter by category, price, availability |
| Add to Cart | Add selected products to cart |
| Update Cart | Modify quantities or remove items |
| Place Order | Confirm checkout and place order |
| Track Order | Track order status with courier info |
| View Orders | View order history and statuses |
| Receive Notifications | Get order updates via UI/email |



### 🧑‍💼 Admin Use Cases

| Use Case | Description |
|-------|-------------|
| Login | Authenticate as admin |
| View Dashboard | System overview (sales & orders) |
| Manage Products | Add/update/delete products |
| Browse Products | View all product listings |
| View Product Details | View product information |
| Manage Categories | Add/update/remove categories |
| View Orders | Monitor all customer orders |
| Manage Orders | Update order status and couriers |
| Manage Users | View and manage user accounts |
| Manage Coupons | Create and manage discount codes |
| View Reports | Analyze sales and performance |

---

## 🧩 7. Technology Stack (Suggested)

- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5  
- **Backend:** PHP  
- **Database:** MySQL  
- **Security:** bcrypt hashing, prepared MYSQL statements  



## 🚀 8. Future Enhancements

- Online payment gateway integration
- Mobile application (Android / iOS)
- Wishlist functionality
- Multi-vendor support
- Advanced analytics dashboard



## 📄 License
This project is developed for academic and commercial use under the **Almas Clothing Brand**.


### 👨‍💻 Developed By
**Ali Raza Tahir(artcodecreator)**  
Front-End & Web Developer  

---

⭐ *If you like this project, don’t forget to star the repository!*
