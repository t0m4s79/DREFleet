# 🚛 Fleet Management System

A fleet management system designed to support the operational needs of our organization. The system handles multiple **entities**, including:
- Drivers
- 🚗 Vehicles
- 🗺️ Places
- 👶 Kids  
...and more!

---

## 📖 Table of Contents

1. [About](#-fleet-management-system)  
2. [Getting Started](#-getting-started)  
   - [Prerequisites](#prerequisites)  
   - [Recommended Reading](#recommended-reading)  
   - [Installation](#-installation)  
3. [Additional Resources](#-additional-resources)  
4. [Entity Relationship Diagram](#-entity-relationship-diagram)  
5. [Features](#-features)

---

## 📚 Getting Started

### Prerequisites

Before setting up the project, ensure you have the following tools installed:

- [Composer](https://getcomposer.org/download/) (PHP dependency manager)  
- [Node.js](https://nodejs.org/en/download/prebuilt-installer) (JavaScript runtime environment)  
- [PHP](https://www.php.net/downloads)  

### Recommended Reading

To better understand how the system works, we recommend reviewing the documentation for the following key libraries/packages used in the project:
- **Laravel Breeze (React)**: [Laravel Breeze Docs](https://laravel.com/docs/11.x/starter-kits#laravel-breeze)
- **React.js**: [React Documentation](https://react.dev/)  
- **Leaflet.js (Map Library)**: [Leaflet Documentation](https://leafletjs.com/)  
- **Material-UI (MUI)**: [MUI Documentation](https://mui.com/material-ui/getting-started/)  
- **OSRM Backend**: [OSRM Documentation](https://project-osrm.org/docs/v5.24.0/api/#) 

---

### 🛠️ Installation

Follow these steps to set up the project locally:

1. **Clone the Repository**  
   Open a terminal and run:
   ```bash
   git clone https://github.com/t0m4s79/DREFleet.git
   cd DREFleet
   ```
2. **Environment setup**

   - Add **composer**, **nodejs** and **php** to your system's environment variables.
   - Copy the `.env.example` file to `.env`
   - Fill in your database credentials in the newly created `.env` file.
   - Generate the Laravel application key with:
   ```bash
   php artisan key:generate
   ```

3. **Install Dependencies**

   Navigate to the Laravel directory and install backend and frontend dependencies:
   ```bash
   cd laravel
   composer install
   npm install
   ```
   
4. **Run Database Migrations**

   Apply the database migrations:
   ```bash
   php artisan migrate
   ```

5. **Start the Servers**

   If possible, to make things easier, open two terminals. One will run the backend server and the other one will run the frontend. Then, run the following commands:
   - Backend
      ```bash
      php artisan serve
      ```
   - Frontend
      ```bash
      npm run dev
      ```

---

## ⚙️ Additional Resources
If you're on Windows and want to automate recurring tasks, such as scheduling database backups, check out this guide:
Windows Task Scheduler Guide

[Windows Task Scheduler](https://gist.github.com/Splode/94bfa9071625e38f7fd76ae210520d94)

### :globe_with_meridians: Setting Up Your Own OSRM Server
For our project, we created a separate instance of the Open Source Routing Machine (OSRM) to calculate optimal routes for vehicles. If you want to set up your own OSRM server, follow the official OSRM backend guide on Github:

GitHub Repository: [OSRM-backend](https://github.com/Project-OSRM/osrm-backend)

---

## 🗂️ Entity Relationship Diagram
Below is the most up-to-date entity relationship diagram for this project:

### Updated Entity Relationship Diagram
![Diagrama Relações](https://github.com/user-attachments/assets/ce139e38-8614-4a6f-a0e8-44670f6f3e79)

---

## ✨ Features
Highlighting the key features of this fleet management system:
- **Driver and Vehicle Management:** Track and manage driver and vehicle information.
- **Interactive Maps:** Plan and optimize routes using Leaflet.js and OSRM.
- **Dynamic DataTables:** Present data with custom columns and filters using MUI DataGrid.
- **Drag-and-Drop:** Rearrange lists and orders interactively.

---


