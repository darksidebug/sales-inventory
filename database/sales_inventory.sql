-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 06, 2020 at 02:23 AM
-- Server version: 10.4.10-MariaDB
-- PHP Version: 7.3.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sales_inventory`
--

-- --------------------------------------------------------

--
-- Table structure for table `customers_table`
--

CREATE TABLE `customers_table` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `nick_name` varchar(50) NOT NULL,
  `profession_business` varchar(250) NOT NULL,
  `color` varchar(50) NOT NULL,
  `size` varchar(50) NOT NULL,
  `address` varchar(150) NOT NULL,
  `contact` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `customers_table`
--

INSERT INTO `customers_table` (`id`, `name`, `nick_name`, `profession_business`, `color`, `size`, `address`, `contact`) VALUES
(4, 'Francisca Colar', 'Fransing', 'Dirx Printing', 'None', '7.5', 'Malangsam Bontoc Southern Leeyte', '09363880171'),
(5, 'Lucila Bacalla', 'nday', 'teacher', 'navy blue', 'Large', 'San Roque Sogod, So. Leyte', '09177064269'),
(6, 'Alona Gonzales', 'Alona', 'housewife', 'red', 'medium', 'malangza bontoc', '09175746911');

-- --------------------------------------------------------

--
-- Table structure for table `orders_table`
--

CREATE TABLE `orders_table` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `item_box_num` varchar(50) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `amount` decimal(10,0) NOT NULL,
  `remarks` varchar(50) NOT NULL,
  `latest_old_order_remarks` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `orders_table`
--

INSERT INTO `orders_table` (`id`, `product_id`, `item_box_num`, `customer_id`, `amount`, `remarks`, `latest_old_order_remarks`) VALUES
(55, 17, '5/22', 4, '1000', 'Returned', 'Old'),
(56, 7, 'Walk-in', 4, '1850', 'Paid', 'Old'),
(57, 13, 'Walk-in', 4, '1550', 'Paid', 'Old'),
(58, 17, '5/22', 4, '1000', 'Returned', 'Old');

-- --------------------------------------------------------

--
-- Table structure for table `payments_history_table`
--

CREATE TABLE `payments_history_table` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `amount_paid` decimal(10,0) NOT NULL,
  `balance` decimal(10,0) NOT NULL,
  `payment_method` varchar(150) NOT NULL,
  `date_paid` varchar(50) NOT NULL,
  `remarks` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `payments_history_table`
--

INSERT INTO `payments_history_table` (`id`, `payment_id`, `customer_id`, `amount_paid`, `balance`, `payment_method`, `date_paid`, `remarks`) VALUES
(44, 27, 4, '1850', '0', 'Palawan', '2020-06-05', 'Partial Payment'),
(45, 28, 4, '1550', '0', 'Online BPI', '2020-06-05', 'Partial Payment');

-- --------------------------------------------------------

--
-- Table structure for table `payments_table`
--

CREATE TABLE `payments_table` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `payment_amount` decimal(10,0) DEFAULT NULL,
  `payment_method` varchar(150) DEFAULT NULL,
  `balance` decimal(10,0) NOT NULL,
  `date_paid` varchar(50) DEFAULT NULL,
  `remarks` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `payments_table`
--

INSERT INTO `payments_table` (`id`, `customer_id`, `payment_amount`, `payment_method`, `balance`, `date_paid`, `remarks`) VALUES
(27, 4, '1850', 'Palawan', '0', '2020-06-05', 'Paid'),
(28, 4, '1550', 'Online BPI', '0', '2020-06-05', 'Paid');

-- --------------------------------------------------------

--
-- Table structure for table `products_table`
--

CREATE TABLE `products_table` (
  `id` int(11) NOT NULL,
  `item_box_num` varchar(150) NOT NULL,
  `item_desc` varchar(250) NOT NULL,
  `brand` varchar(150) NOT NULL,
  `size` varchar(150) NOT NULL,
  `color` varchar(50) NOT NULL,
  `price` decimal(10,0) NOT NULL,
  `image_name` varchar(150) DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `remarks` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `products_table`
--

INSERT INTO `products_table` (`id`, `item_box_num`, `item_desc`, `brand`, `size`, `color`, `price`, `image_name`, `date_added`, `remarks`) VALUES
(7, 'Walk-in', 'Ladies Shoes', 'Sketchers', '8', 'Black', '1850', NULL, '2020-06-06 00:03:48', 'Sold'),
(13, 'Walk-in', 'Ladies Shoes', 'Katie and Kally', '7', 'Checkered Blue', '1550', NULL, '2020-06-06 00:21:13', 'Sold'),
(14, 'Walk-in', 'Cross Body', 'Coach', 'Small', 'Brown', '1850', NULL, '2020-06-06 00:02:05', 'Unsold'),
(15, 'Walk-in', 'Cross Body Bag', 'Coach', 'Small', 'White', '1650', NULL, '2020-06-05 23:38:47', 'Unsold'),
(16, 'Walk-in', '2 T-shirts @ 950', 'Aeropostale ', 'Medium', 'Blue and Blue', '1900', NULL, '2020-06-06 00:02:26', 'Unsold'),
(17, '5/22', 'tshirt', 'hollister', 'medium', 'red', '1000', '', '2020-06-06 00:21:59', 'Unsold');

-- --------------------------------------------------------

--
-- Table structure for table `user_account`
--

CREATE TABLE `user_account` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `user_pass` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user_account`
--

INSERT INTO `user_account` (`id`, `username`, `user_pass`) VALUES
(2, 'system_admin', '$2y$10$P4VBl.scmG0MjPmrW3cKcuDEF84K5e4b8cI.q9zHc.7y34CVfAtm6');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customers_table`
--
ALTER TABLE `customers_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders_table`
--
ALTER TABLE `orders_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments_history_table`
--
ALTER TABLE `payments_history_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments_table`
--
ALTER TABLE `payments_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products_table`
--
ALTER TABLE `products_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_account`
--
ALTER TABLE `user_account`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customers_table`
--
ALTER TABLE `customers_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders_table`
--
ALTER TABLE `orders_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `payments_history_table`
--
ALTER TABLE `payments_history_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `payments_table`
--
ALTER TABLE `payments_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `products_table`
--
ALTER TABLE `products_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `user_account`
--
ALTER TABLE `user_account`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
