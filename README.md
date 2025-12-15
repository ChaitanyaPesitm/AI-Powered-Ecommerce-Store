# 🛍️ AI-Powered Ecommerce Store

A next-generation e-commerce platform built with **PHP** and **Artificial Intelligence**, featuring voice-controlled navigation, smart product recommendations, and real-time review analysis.

![Project Status](https://img.shields.io/badge/status-active-success.svg)
![License](https://img.shields.io/badge/license-MIT-blue.svg)

## 🌟 Key Features

### 🤖 AI Capabilities
- **Voice Copilot**: Navigate the site, search for products, and manage your cart using natural voice commands.
- **Smart Recommendations**: Personalized product suggestions based on browsing history and trends.
- **Review Analyzer**: Instantly summarizes hundreds of customer reviews into pros, cons, and a final verdict using AI.
- **Intelligent Search**: Context-aware product search that understands user intent.

### 🛒 E-commerce Engine
- **Secure Checkout**: Integrated **Razorpay** payment gateway and Cash on Delivery (COD) support.
- **Dynamic Cart**: Real-time cart management with instant calculations.
- **Order Tracking**: Detailed order history and real-time status updates for customers.
- **3D Product Views**: Interactive 3D model viewer for immersive product exploration.

### 🛡️ Admin Dashboard
- **Analytics Hub**: Visual charts for sales, revenue, and order trends.
- **Product Management**: Easy-to-use interface for adding, editing, and deleting products.
- **Order Control**: Manage order statuses (Pending, Shipped, Delivered) and handle returns.

## 🛠️ Tech Stack

- **Backend**: Native PHP (8.0+)
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **AI Integration**: Groq API (Llama 3.3 70B), Google Gemini (Experimental)
- **Payments**: Razorpay API
- **Server**: Apache (via XAMPP/WAMP)

## 🚀 Installation & Setup

1.  **Clone the Repository**
    ```bash
    git clone https://github.com/ChaitanyaPesitm/AI-Powered-Ecommerce-Store.git
    cd AI-Powered-Ecommerce-Store
    ```

2.  **Configure Database**
    - Open `phpMyAdmin` and create a database named `ecommerce_db`.
    - Import the SQL file located at: `config/database/ecomerce.sql`.

3.  **Setup Configuration**
    - Edit `config/db.php` with your database credentials.
    - Rename `config/razorpay_config.php.example` (if available) or create `config/razorpay_config.php` with your **Razorpay API Keys**.
    - Configure `config/ai.php` with your **Groq API Key**.

4.  **Run the Application**
    - Move the project folder to your server's root directory (e.g., `C:\xampp\htdocs\ecommerce`).
    - Visit `http://localhost/ecommerce` in your browser.

## 🔑 Default Credentials

**Admin Panel**: `http://localhost/ecommerce/admin/login.php`
- **Email**: `admin@admin.com`
- **Password**: `password` (check database for actual hash if changed)

## 📸 Screenshots

*(Add screenshots of your Home Page, AI Chat, and Admin Dashboard here)*

## 🤝 Contributing

Contributions are welcome! Please fork the repository and create a pull request for any feature enhancements or bug fixes.

## 📜 License

This project is licensed under the MIT License.
