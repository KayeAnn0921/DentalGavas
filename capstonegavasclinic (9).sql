-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 12, 2025 at 03:06 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `capstonegavasclinic`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `type_of_visit` enum('appointment','walk-in') NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `classification_id` int(11) NOT NULL,
  `status` enum('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  `service_id` int(11) NOT NULL,
  `doctor` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `first_name`, `last_name`, `patient_id`, `type_of_visit`, `appointment_date`, `appointment_time`, `contact_number`, `classification_id`, `status`, `service_id`, `doctor`) VALUES
(1, 'kaye', 'tuquib', 1, 'appointment', '2025-05-07', '03:20:19', '0931432422', 22, 'pending', 0, ''),
(24, 'kayee', 'tuiw', 1, 'appointment', '2025-05-23', '03:48:00', '09386842219', 0, 'pending', 29, ''),
(25, 'Michelle', 'Cebritas', 1, 'appointment', '2025-10-17', '21:51:00', '09386842219', 0, 'confirmed', 28, ''),
(26, 'Ron', 'Tzy', 1, 'appointment', '2025-05-12', '10:17:00', '09386842219', 0, 'pending', 27, 'Dr. Anna Patricia Gavas-Paña'),
(28, 'marc', 'pelonits', 1, 'appointment', '2025-05-12', '08:47:00', '09386842219', 0, 'confirmed', 27, 'Dr. Anna Patricia Gavas-Paña');

-- --------------------------------------------------------

--
-- Table structure for table `billing`
--

CREATE TABLE `billing` (
  `billing_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `amount_paid` decimal(10,2) NOT NULL,
  `balance` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `billing`
--

INSERT INTO `billing` (`billing_id`, `patient_id`, `service_id`, `total`, `discount`, `amount_paid`, `balance`, `created_at`) VALUES
(1, 13, 0, 6540.00, 60.00, 5000.00, 1540.00, '2025-05-10 04:50:03'),
(2, 14, 0, 5980.00, 20.00, 300.00, 5680.00, '2025-05-11 15:50:51');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `name`) VALUES
(1, 'Surgery'),
(2, 'prosthodontics'),
(3, 'consulatation'),
(4, 'oral'),
(5, 'fluorization'),
(6, 'root canal'),
(7, 'restortive'),
(9, 'bleaching'),
(10, 'radiograph');

-- --------------------------------------------------------

--
-- Table structure for table `condition_codes`
--

CREATE TABLE `condition_codes` (
  `code_id` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `condition_codes`
--

INSERT INTO `condition_codes` (`code_id`, `code`, `name`, `description`, `color`) VALUES
(1, 'AM', 'Amalgam', 'Amalgam filling', '#808080'),
(2, 'COM', 'Composite', 'Composite filling', '#FFFFFF'),
(3, 'IMP', 'Impacted', 'Impacted tooth', NULL),
(4, 'UN', 'Unerupted', 'Unerupted tooth', NULL),
(5, 'LCF', 'Lightcured Filled', 'Light-cured filling', '#FFFFFF'),
(6, 'FB', 'Fixed Bridge', 'Fixed bridge', NULL),
(7, 'RC', 'Recurrent Caries', 'Recurrent caries', '#FFFF00'),
(8, 'RF', 'Root Fragment', 'Root fragment', NULL),
(9, 'M', 'Missing', 'Missing tooth', NULL),
(10, 'RCT', 'Root Canal Therapy', 'Root canal therapy', '#0000FF'),
(11, 'JC', 'Jacket Crown', 'Jacket crown', NULL),
(12, 'MB', 'Maryland Bridge', 'Maryland bridge', NULL),
(13, 'X', 'Extraction', 'Tooth indicated for extraction', '#FF0000'),
(14, 'F', 'Filling', 'Tooth indicated for filling', '#00FF00'),
(15, 'OP', 'Oral Prophylaxis', 'Oral prophylaxis', NULL),
(16, 'RPD', 'Removal Partial Denture', 'Removal partial denture with casted clasp', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `dental_charts`
--

CREATE TABLE `dental_charts` (
  `chart_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `chart_date` date NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doctor_schedule`
--

CREATE TABLE `doctor_schedule` (
  `id` int(11) NOT NULL,
  `doctor_name` varchar(100) NOT NULL,
  `schedule_date` date NOT NULL,
  `status` enum('Available','Not Available') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctor_schedule`
--

INSERT INTO `doctor_schedule` (`id`, `doctor_name`, `schedule_date`, `status`, `start_time`, `end_time`) VALUES
(1, 'Dr. Anna Patricia Gavas-Paña', '2025-05-12', 'Available', '08:47:00', '19:51:00'),
(2, 'Dr. Anna Patricia Gavas-Paña', '2025-05-13', 'Available', '08:47:00', '19:51:00'),
(4, 'Dr. Glenn Gavas', '2025-05-01', 'Not Available', '22:55:00', '17:55:00');

-- --------------------------------------------------------

--
-- Table structure for table `health_conditions`
--

CREATE TABLE `health_conditions` (
  `condition_id` int(11) NOT NULL,
  `health_questionnaire_id` int(11) NOT NULL,
  `condition_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `health_conditions`
--

INSERT INTO `health_conditions` (`condition_id`, `health_questionnaire_id`, `condition_name`) VALUES
(18, 15, 'High Blood Pressure'),
(19, 15, 'Epilepsy/Convulsions'),
(20, 15, 'Respiratory Problems'),
(21, 15, 'Hepatitis/Jaundice'),
(22, 16, 'Hepatitis/Liver Disease'),
(23, 16, 'Hay Fever/Allergies');

-- --------------------------------------------------------

--
-- Table structure for table `health_questionnaire`
--

CREATE TABLE `health_questionnaire` (
  `health_questionnaire_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `good_health` enum('Yes','No') DEFAULT NULL,
  `medical_condition` enum('Yes','No') DEFAULT NULL,
  `medical_condition_details` varchar(255) DEFAULT NULL,
  `serious_illness` enum('Yes','No') DEFAULT NULL,
  `serious_illness_details` varchar(255) DEFAULT NULL,
  `hospitalized` enum('Yes','No') DEFAULT NULL,
  `hospitalized_details` varchar(255) DEFAULT NULL,
  `medication` enum('Yes','No') DEFAULT NULL,
  `medication_details` varchar(255) DEFAULT NULL,
  `smoke` enum('Yes','No') DEFAULT NULL,
  `alcohol` enum('Yes','No') DEFAULT NULL,
  `drugs` enum('Yes','No') DEFAULT NULL,
  `allergy` enum('Yes','No') DEFAULT NULL,
  `allergy_details` varchar(255) DEFAULT NULL,
  `pregnant` enum('Yes','No') DEFAULT NULL,
  `nursing` enum('Yes','No') DEFAULT NULL,
  `birth_control` enum('Yes','No') DEFAULT NULL,
  `condition_list` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `health_questionnaire`
--

INSERT INTO `health_questionnaire` (`health_questionnaire_id`, `patient_id`, `good_health`, `medical_condition`, `medical_condition_details`, `serious_illness`, `serious_illness_details`, `hospitalized`, `hospitalized_details`, `medication`, `medication_details`, `smoke`, `alcohol`, `drugs`, `allergy`, `allergy_details`, `pregnant`, `nursing`, `birth_control`, `condition_list`) VALUES
(15, 14, 'No', 'No', '', 'No', '', 'No', '', 'No', '', 'No', 'No', 'No', 'No', '', 'Yes', 'Yes', 'Yes', NULL),
(16, 14, 'No', 'No', '', 'No', '', 'No', '', 'No', '', 'No', 'No', 'No', 'No', '', 'No', 'No', 'No', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `medications`
--

CREATE TABLE `medications` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `dosage` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medications`
--

INSERT INTO `medications` (`id`, `name`, `dosage`, `description`) VALUES
(9, 'Mefinamic', '500mg', 'For pain killer');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL,
  `visit_type` varchar(255) DEFAULT NULL,
  `visit_date` date DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `civil_status` varchar(50) DEFAULT NULL,
  `mobile_number` varchar(15) DEFAULT NULL,
  `email_address` varchar(100) DEFAULT NULL,
  `fb_account` varchar(100) DEFAULT NULL,
  `home_address` text DEFAULT NULL,
  `work_address` text DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `office_contact_number` varchar(15) DEFAULT NULL,
  `parent_guardian_name` varchar(255) DEFAULT NULL,
  `physician_name` varchar(255) DEFAULT NULL,
  `physician_address` text DEFAULT NULL,
  `previous_dentists` text DEFAULT NULL,
  `treatment_done` text DEFAULT NULL,
  `referred_by` varchar(255) DEFAULT NULL,
  `last_dental_visit` date DEFAULT NULL,
  `reason_for_visit` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`patient_id`, `visit_type`, `visit_date`, `last_name`, `first_name`, `middle_name`, `birthdate`, `age`, `sex`, `civil_status`, `mobile_number`, `email_address`, `fb_account`, `home_address`, `work_address`, `occupation`, `office_contact_number`, `parent_guardian_name`, `physician_name`, `physician_address`, `previous_dentists`, `treatment_done`, `referred_by`, `last_dental_visit`, `reason_for_visit`) VALUES
(13, 'Walk-in', '2025-05-02', 'Rintarou', 'Okabe', 'Ron', '2001-01-22', 24, 'Male', 'Single', '0903434343', '', '', 'apopong', '', '', '', '', '', '', '', '', '', '0000-00-00', ''),
(14, 'Walk-in', '2025-05-02', 'tset', ' John', 'Ron', '2001-09-01', 23, 'Male', 'Single', '0903434343', '', '', 'MALAPATAYN', '', '', '', '', '', '', '', '', '', '0000-00-00', ''),
(15, 'Appointment', '2025-05-11', 'Cebritas', 'Michelle', 'revs', '2010-09-21', 14, 'Male', 'Married', '09386842219', 'bb@gmail.com', 'song', 'N/A', 'N/A', 'driver', 'n/a', 'EMMA', 'KEY', 'N/A', 'lol', 'kaka', 'kak', '2025-05-11', 'SAKIT\r\n');

-- --------------------------------------------------------

--
-- Table structure for table `patient_services`
--

CREATE TABLE `patient_services` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_services`
--

INSERT INTO `patient_services` (`id`, `patient_id`, `service_id`) VALUES
(5, 13, 27),
(6, 13, 28),
(7, 14, 27),
(8, 15, 29);

-- --------------------------------------------------------

--
-- Table structure for table `prescription`
--

CREATE TABLE `prescription` (
  `prescription_id` int(11) NOT NULL,
  `sig` varchar(50) NOT NULL,
  `quantity` int(12) NOT NULL,
  `med_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prescription`
--

INSERT INTO `prescription` (`prescription_id`, `sig`, `quantity`, `med_id`, `patient_id`) VALUES
(13, '2xa day', 20, 9, 13),
(14, '2x a day after meal', 20, 9, 15),
(15, '2x a day', 20, 9, 14),
(16, '42424', 3, 9, 15);

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `name`, `price`, `parent_id`, `category_id`) VALUES
(27, 'porcelain', 6000.00, NULL, 2),
(28, 'anterior', 600.00, 27, 2),
(29, 'posterior', 6000.00, 27, 2),
(30, 'Surgery', 800.00, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tooth_conditions`
--

CREATE TABLE `tooth_conditions` (
  `condition_id` int(11) NOT NULL,
  `chart_id` int(11) NOT NULL,
  `tooth_number` varchar(5) NOT NULL,
  `condition_code` varchar(10) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tooth_treatments`
--

CREATE TABLE `tooth_treatments` (
  `treatment_id` int(11) NOT NULL,
  `chart_id` int(11) NOT NULL,
  `tooth_number` varchar(5) NOT NULL,
  `service_id` int(11) NOT NULL,
  `treatment_date` date NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `type`) VALUES
(5, 'afsa', 'sfas', 'Secretary');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `classification_id` (`classification_id`);

--
-- Indexes for table `billing`
--
ALTER TABLE `billing`
  ADD PRIMARY KEY (`billing_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `condition_codes`
--
ALTER TABLE `condition_codes`
  ADD PRIMARY KEY (`code_id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `dental_charts`
--
ALTER TABLE `dental_charts`
  ADD PRIMARY KEY (`chart_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `doctor_schedule`
--
ALTER TABLE `doctor_schedule`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `health_conditions`
--
ALTER TABLE `health_conditions`
  ADD PRIMARY KEY (`condition_id`),
  ADD KEY `health_questionnaire_id` (`health_questionnaire_id`);

--
-- Indexes for table `health_questionnaire`
--
ALTER TABLE `health_questionnaire`
  ADD PRIMARY KEY (`health_questionnaire_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `medications`
--
ALTER TABLE `medications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`patient_id`);

--
-- Indexes for table `patient_services`
--
ALTER TABLE `patient_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `prescription`
--
ALTER TABLE `prescription`
  ADD PRIMARY KEY (`prescription_id`),
  ADD KEY `med_id` (`med_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `tooth_conditions`
--
ALTER TABLE `tooth_conditions`
  ADD PRIMARY KEY (`condition_id`),
  ADD KEY `chart_id` (`chart_id`);

--
-- Indexes for table `tooth_treatments`
--
ALTER TABLE `tooth_treatments`
  ADD PRIMARY KEY (`treatment_id`),
  ADD KEY `chart_id` (`chart_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `billing`
--
ALTER TABLE `billing`
  MODIFY `billing_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `condition_codes`
--
ALTER TABLE `condition_codes`
  MODIFY `code_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `dental_charts`
--
ALTER TABLE `dental_charts`
  MODIFY `chart_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `doctor_schedule`
--
ALTER TABLE `doctor_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `health_conditions`
--
ALTER TABLE `health_conditions`
  MODIFY `condition_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `health_questionnaire`
--
ALTER TABLE `health_questionnaire`
  MODIFY `health_questionnaire_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `medications`
--
ALTER TABLE `medications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `patient_services`
--
ALTER TABLE `patient_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `prescription`
--
ALTER TABLE `prescription`
  MODIFY `prescription_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `tooth_conditions`
--
ALTER TABLE `tooth_conditions`
  MODIFY `condition_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tooth_treatments`
--
ALTER TABLE `tooth_treatments`
  MODIFY `treatment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `billing`
--
ALTER TABLE `billing`
  ADD CONSTRAINT `billing_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE;

--
-- Constraints for table `dental_charts`
--
ALTER TABLE `dental_charts`
  ADD CONSTRAINT `dental_charts_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  ADD CONSTRAINT `dental_charts_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `health_conditions`
--
ALTER TABLE `health_conditions`
  ADD CONSTRAINT `health_conditions_ibfk_1` FOREIGN KEY (`health_questionnaire_id`) REFERENCES `health_questionnaire` (`health_questionnaire_id`) ON DELETE CASCADE;

--
-- Constraints for table `health_questionnaire`
--
ALTER TABLE `health_questionnaire`
  ADD CONSTRAINT `health_questionnaire_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`);

--
-- Constraints for table `patient_services`
--
ALTER TABLE `patient_services`
  ADD CONSTRAINT `patient_services_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `patient_services_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON DELETE CASCADE;

--
-- Constraints for table `prescription`
--
ALTER TABLE `prescription`
  ADD CONSTRAINT `prescription_ibfk_1` FOREIGN KEY (`med_id`) REFERENCES `medications` (`id`),
  ADD CONSTRAINT `prescription_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`);

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `services` (`service_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `services_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`);

--
-- Constraints for table `tooth_conditions`
--
ALTER TABLE `tooth_conditions`
  ADD CONSTRAINT `tooth_conditions_ibfk_1` FOREIGN KEY (`chart_id`) REFERENCES `dental_charts` (`chart_id`);

--
-- Constraints for table `tooth_treatments`
--
ALTER TABLE `tooth_treatments`
  ADD CONSTRAINT `tooth_treatments_ibfk_1` FOREIGN KEY (`chart_id`) REFERENCES `dental_charts` (`chart_id`),
  ADD CONSTRAINT `tooth_treatments_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`),
  ADD CONSTRAINT `tooth_treatments_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
