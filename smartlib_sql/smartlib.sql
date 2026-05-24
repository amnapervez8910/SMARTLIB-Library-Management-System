-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 06, 2026 at 01:10 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smartlib`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `book_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `isbn` varchar(100) DEFAULT NULL,
  `publisher` varchar(255) DEFAULT NULL,
  `year_published` int(11) DEFAULT NULL,
  `copies_available` int(11) DEFAULT NULL,
  `status` enum('available','borrowed','requested') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`book_id`, `title`, `author`, `category`, `isbn`, `publisher`, `year_published`, `copies_available`, `status`) VALUES
(4, 'The Great Gatsby', 'F. Scott Fitzgerald', 'Classic', '978-0743273565', 'Scribner', 1925, 3, 'available'),
(5, 'Sapiens', 'Yuval Noah Harari', 'Non-Fiction', '978-0062316097', 'Harper', 2015, 4, 'available'),
(6, 'Harry Potter and the Sorcerer\'s Stone', 'J.K. Rowling', 'Fantasy', '978-0590353427', 'Scholastic', 1998, 10, 'available'),
(7, 'Clean Code', 'Robert C. Martin', 'Technology', '978-0132350884', 'Prentice Hall', 2008, 0, ''),
(8, 'Dune', 'Frank Herbert', 'Sci-Fi', '978-0441172719', 'Ace', 1965, 3, 'borrowed'),
(9, 'The Alchemist', 'Paulo Coelho', 'Fiction', '978-0062315007', 'HarperOne', 1988, 6, 'requested'),
(10, 'Educated', 'Tara Westover', 'Memoir', '978-0399590504', 'Random House', 2018, 2, 'requested'),
(11, 'The Hobbit', 'J.R.R. Tolkien', 'Fantasy', '978-0547928227', 'Mariner Books', 1937, 4, 'requested'),
(14, 'A Game of Thrones', 'George R.R. Martin', 'Fantasy', '9780553103540', 'Bantam Books', 1996, 7, 'available'),
(16, 'Brave New World', 'Aldous Huxley', 'Dystopian', '9780060850524', 'Chatto & Windus', 1932, 3, 'requested'),
(17, 'The Hunger Games', 'Suzanne Collins', 'Dystopian', '9780439023528', 'Scholastic Press', 2008, 5, 'available'),
(18, 'Pride and Prejudice', 'Jane Austen', 'Romance', '9780141439518', 'T. Egerton', 1813, 3, 'requested'),
(19, 'Me Before You', 'Jojo Moyes', 'Romance', '9780718157838', 'Penguin Books', 2012, 4, 'available'),
(20, 'The Da Vinci Code', 'Dan Brown', 'Thriller', '9780307474278', 'Doubleday', 2003, 4, 'requested'),
(21, 'Gone Girl', 'Gillian Flynn', 'Thriller', '9780307588371', 'Crown Publishing', 2012, 2, 'available'),
(22, 'The Fault in Our Stars', 'John Green', 'Young adult', '9780525478812', 'Dutton Books', 2012, 8, 'available'),
(23, 'Divergent', 'Veronica Roth', 'Young adult', '9780062024039', 'Katherine Tegen Books', 2011, 6, 'available'),
(25, 'The Circle', 'Dave Eggers', 'Tech thriller', '9780385351392', 'Knopf', 2013, 2, 'requested'),
(26, 'Digital Fortress', 'Dan Brown', 'Tech thriller', '9780312335167', 'St. Martin\'s Press', 1998, 3, 'requested');

-- --------------------------------------------------------

--
-- Table structure for table `book_recommendations`
--

CREATE TABLE `book_recommendations` (
  `recommend_id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `book_title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `priority` varchar(50) DEFAULT 'normal',
  `comments` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `issued_books`
--

CREATE TABLE `issued_books` (
  `issue_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `issue_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `status` enum('issued','returned','overdue','requested') DEFAULT 'issued'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `issued_books`
--

INSERT INTO `issued_books` (`issue_id`, `member_id`, `book_id`, `issue_date`, `due_date`, `return_date`, `status`) VALUES
(29, 5, 24, '2025-12-23', '2026-01-22', NULL, 'issued'),
(30, 5, 16, '2025-12-26', '2026-01-25', NULL, 'issued'),
(33, 4, 26, '2025-12-27', '2026-01-26', NULL, 'issued'),
(34, 3, 21, '2025-12-27', '2026-01-10', NULL, 'issued'),
(35, 3, 18, '2025-12-27', '2026-01-10', NULL, 'issued');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `member_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `registration_date` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`member_id`, `name`, `email`, `phone`, `address`, `role`, `password`, `registration_date`) VALUES
(3, 'Student', 'student@smartlib.com', '03000000000', 'N/A', 'student', '$2y$10$1tZ5aiTbnTJhvlSzqfZuaOaHvaiESnU0gpIrl7mbLJ3bYxsAd9Yoe', '2025-12-12 13:43:04'),
(4, 'Faculty 1', 'faculty@smartlib.com', '03000000000', 'N/A', 'faculty', '$2y$10$W/BI/FtwmAQMy29iHwBiFeRgEEm0g60.A4WUCvbbyYfQRHGsUvwSK', '2025-12-16 16:46:00'),
(5, 'Librarian 1', 'library@smartlib.com', NULL, NULL, 'librarian', '$2y$10$2E6NFaL8kA/0F3WlfV.1su4O.KBVkeFeKj6gEaAAqAFIGtBqLO/Ti', '');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `report_type` varchar(255) NOT NULL,
  `generated_by` varchar(255) DEFAULT NULL,
  `generated_on` timestamp NOT NULL DEFAULT current_timestamp(),
  `details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `request_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `book_title` varchar(255) DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `request_date` date DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `priority` varchar(20) DEFAULT NULL,
  `request_type` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `reservation_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `reservation_date` date DEFAULT NULL,
  `status` enum('pending','approved','cancelled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `statistics`
--

CREATE TABLE `statistics` (
  `stat_id` int(11) NOT NULL,
  `total_books` int(11) DEFAULT NULL,
  `total_issued` int(11) DEFAULT NULL,
  `total_requests` int(11) DEFAULT NULL,
  `last_updated` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`book_id`);

--
-- Indexes for table `book_recommendations`
--
ALTER TABLE `book_recommendations`
  ADD PRIMARY KEY (`recommend_id`),
  ADD KEY `fk_faculty_recommend` (`faculty_id`);

--
-- Indexes for table `issued_books`
--
ALTER TABLE `issued_books`
  ADD PRIMARY KEY (`issue_id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`member_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`request_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`reservation_id`);

--
-- Indexes for table `statistics`
--
ALTER TABLE `statistics`
  ADD PRIMARY KEY (`stat_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `book_recommendations`
--
ALTER TABLE `book_recommendations`
  MODIFY `recommend_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `issued_books`
--
ALTER TABLE `issued_books`
  MODIFY `issue_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `statistics`
--
ALTER TABLE `statistics`
  MODIFY `stat_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `book_recommendations`
--
ALTER TABLE `book_recommendations`
  ADD CONSTRAINT `fk_faculty_recommend` FOREIGN KEY (`faculty_id`) REFERENCES `members` (`member_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
