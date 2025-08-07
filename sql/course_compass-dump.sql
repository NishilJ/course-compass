-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Aug 07, 2025 at 08:19 PM
-- Server version: 8.0.41
-- PHP Version: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `course_compass`
--

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

CREATE TABLE `course` (
  `course_id` int NOT NULL,
  `course_prefix` varchar(10) NOT NULL,
  `course_credits` int NOT NULL,
  `course_subject` varchar(255) NOT NULL,
  `course_number` int NOT NULL,
  `course_title` varchar(255) NOT NULL,
  `course_description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `course`
--

INSERT INTO `course` (`course_id`, `course_prefix`, `course_credits`, `course_subject`, `course_number`, `course_title`, `course_description`) VALUES
(1, 'CS', 3, 'Computer Science', 1337, 'Introduction to Programming', 'An introduction to programming using structured languages; covers basic control structures, data types, and problem-solving techniques.'),
(2, 'CS', 3, 'Computer Science', 3345, 'Data Structures & Algorithms', 'Analysis of algorithms including time complexity and Big‑O notation; implementation of stacks, queues, trees (including B‑trees), heaps, hashing, and graph algorithms. Emphasizes design and implementation.'),
(3, 'SE', 3, 'Software Engineering', 3306, 'Software Project Management', 'Focuses on processes and tools for successful software project planning, scheduling, risk analysis, and quality assurance.'),
(4, 'CS', 3, 'Computer Science', 4375, 'Operating Systems', 'Study of modern operating system concepts including process management, scheduling, synchronization, memory management, file systems, and device drivers.'),
(5, 'CS', 3, 'Computer Science', 4485, 'Computer Networks', 'Teaches principles of data communications and networking: protocols, layering, switching, routing, and TCP/IP architecture.'),
(6, 'SE', 3, 'Software Engineering', 4351, 'Requirements Engineering', 'Covers elicitation, specification, and validation of software requirements; modeling techniques and managing stakeholder interactions.'),
(7, 'CS', 3, 'Computer Science', 3380, 'Database Systems', 'Introduction to relational database design, SQL querying, normalization, transactions, indexing, and basic database connectivity.'),
(8, 'SE', 3, 'Software Engineering', 4381, 'Software Testing', 'Principles and practices of software testing; test planning, test case design, fault analysis, and automated testing tools.'),
(9, 'CS', 3, 'Computer Science', 4301, 'Programming Languages', 'Explores design concepts of modern programming languages; syntax, semantics, paradigms, type systems, and language translation.'),
(10, 'SE', 3, 'Software Engineering', 4365, 'Agile Software Development', 'Covers agile methodologies such as Scrum and Kanban; emphasis on iterative development, user stories, and continuous integration.'),
(11, 'CS', 3, 'Computer Science', 4390, 'Artificial Intelligence', 'Survey of foundational AI topics: search, knowledge representation, machine learning basics, reasoning, and planning techniques.'),
(12, 'SE', 3, 'Software Engineering', 4305, 'Object-Oriented Design', 'Design of object-oriented software using UML; covers design patterns, abstractions, and modular system architecture.'),
(13, 'CS', 3, 'Computer Science', 4310, 'Computer Architecture', 'Examines computer organization: instruction sets, pipelining, memory hierarchy, and performance metrics.'),
(14, 'CS', 3, 'Computer Science', 4330, 'Mobile App Development', 'Design and development of mobile applications for Android and iOS; covers UI frameworks, APIs, and deployment.'),
(15, 'SE', 3, 'Software Engineering', 4320, 'Software Design Patterns', 'Study of object-oriented design patterns such as Singleton, Factory, Observer and how to apply them in real-world software.'),
(16, 'CS', 1, 'Computer Science', 4328, 'Operating Systems Lab', 'Laboratory course accompanying OS theory; covers system calls, kernel modules, process control, and concurrency exercises.'),
(17, 'SE', 3, 'Software Engineering', 4345, 'Human-Centered Design', 'Human-centered design methodology; user research, prototyping, usability testing, and iterative interface design.'),
(18, 'CS', 3, 'Computer Science', 4315, 'Machine Learning', 'Introduction to supervised and unsupervised learning, models including regression, decision trees, clustering, and evaluation metrics.'),
(19, 'SE', 3, 'Software Engineering', 4361, 'DevOps', 'Principles and practices of DevOps: continuous integration, continuous deployment, infrastructure as code, and monitoring.'),
(20, 'CS', 3, 'Computer Science', 4380, 'Computer Graphics', 'Fundamentals of rendering, modeling, transformation, shading, and animation; includes real-time graphics techniques.'),
(21, 'SE', 3, 'Software Engineering', 4391, 'Cloud Computing', 'Concepts and architecture of cloud platforms, virtualization, distributed systems, microservices, and orchestration.'),
(22, 'CS', 3, 'Computer Science', 4309, 'Parallel Computing', 'Principles of parallel algorithms, multi-core processing, synchronization, performance metrics, and programming models.'),
(23, 'SE', 3, 'Software Engineering', 4355, 'Engineering Ethics', 'Study of ethical issues in computing professions including privacy, intellectual property, and social impact.'),
(24, 'CS', 3, 'Computer Science', 4378, 'Cybersecurity Principles', 'Cybersecurity foundations; covers threat modeling, encryption, authentication, network security, and incident response.'),
(25, 'SE', 3, 'Software Engineering', 4382, 'Advanced Testing', 'Advanced software testing strategies: automated testing frameworks, fuzz testing, performance and security testing.'),
(26, 'CS', 3, 'Computer Science', 4386, 'Big Data Analytics', 'Analyzing large-scale data systems; covers data processing pipelines, analytics frameworks, and visualization.'),
(27, 'SE', 3, 'Software Engineering', 4373, 'Secure Software Engineering', 'Secure design principles, threat mitigation, code reviews, and security validation in software lifecycle.'),
(28, 'CS', 3, 'Computer Science', 4306, 'Compiler Design', 'Techniques for designing compilers: lexical analysis, parsing, semantic analysis, optimization, and code generation.'),
(29, 'SE', 3, 'Software Engineering', 4393, 'Continuous Integration', 'Implementation of CI pipelines, automated building, testing infrastructure, and version control practices.'),
(30, 'CS', 3, 'Computer Science', 4326, 'Digital Forensics', 'Principles of digital investigation: crime scene data collection, analysis tools, network forensics, and evidence preservation.');

-- --------------------------------------------------------

--
-- Table structure for table `instructor`
--

CREATE TABLE `instructor` (
  `instructor_id` int NOT NULL,
  `instructor_name` varchar(255) NOT NULL,
  `instructor_phone` bigint DEFAULT NULL,
  `instructor_email` varchar(255) DEFAULT NULL,
  `instructor_office` varchar(255) DEFAULT NULL,
  `instructor_dep` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `instructor`
--

INSERT INTO `instructor` (`instructor_id`, `instructor_name`, `instructor_phone`, `instructor_email`, `instructor_office`, `instructor_dep`) VALUES
(1, 'Dr. Jane Smith', 9728831234, 'jane.smith@utdallas.edu', 'ECSW 3.210', 'Computer Science'),
(2, 'Dr. Alan Lee', 9728835678, 'alan.lee@utdallas.edu', 'ECSS 2.312', 'Software Engineering'),
(3, 'Dr. Emily Johnson', 9728834321, 'emily.johnson@utdallas.edu', 'ECSS 3.420', 'Computer Science'),
(4, 'Dr. Robert Kim', 9728838765, 'robert.kim@utdallas.edu', 'ECSW 2.110', 'Software Engineering'),
(5, 'Dr. Olivia Chen', 9728831111, 'olivia.chen@utdallas.edu', 'ECSS 3.100', 'Computer Science'),
(6, 'Dr. Mark Patel', 9728832222, 'mark.patel@utdallas.edu', 'ECSN 1.321', 'Software Engineering'),
(7, 'Dr. Sarah Park', 9728833333, 'sarah.park@utdallas.edu', 'ECSS 3.210', 'Computer Science'),
(8, 'Dr. Kevin White', 9728834444, 'kevin.white@utdallas.edu', 'ECSN 2.100', 'Software Engineering'),
(9, 'Dr. Rachel Adams', 9728835555, 'rachel.adams@utdallas.edu', 'ECSW 1.215', 'Computer Science'),
(10, 'Dr. Henry Zhou', 9728836666, 'henry.zhou@utdallas.edu', 'ECSS 2.330', 'Software Engineering'),
(11, 'Dr. Anita Rao', 9728837777, 'anita.rao@utdallas.edu', 'ECSN 3.120', 'Computer Science'),
(12, 'Dr. Brian Wong', 9728838888, 'brian.wong@utdallas.edu', 'ECSS 1.110', 'Software Engineering'),
(13, 'Dr. Laura Kim', 9728839999, 'laura.kim@utdallas.edu', 'ECSW 2.112', 'Computer Science'),
(14, 'Dr. James Lee', 9728840001, 'james.lee@utdallas.edu', 'ECSS 3.331', 'Software Engineering'),
(15, 'Dr. Priya Shah', 9728840002, 'priya.shah@utdallas.edu', 'ECSN 1.101', 'Computer Science'),
(16, 'Dr. Tom Nguyen', 9728840003, 'tom.nguyen@utdallas.edu', 'ECSW 3.213', 'Software Engineering'),
(17, 'Dr. Amy Patel', 9728840004, 'amy.patel@utdallas.edu', 'ECSW 1.201', 'Computer Science'),
(18, 'Dr. Jason Reed', 9728840005, 'jason.reed@utdallas.edu', 'ECSS 2.101', 'Software Engineering'),
(19, 'Dr. Nina Gupta', 9728840006, 'nina.gupta@utdallas.edu', 'ECSN 2.114', 'Computer Science'),
(20, 'Dr. Omar Sanchez', 9728840007, 'omar.sanchez@utdallas.edu', 'ECSN 3.001', 'Software Engineering'),
(21, 'Dr. Chloe Bennett', 9728840008, 'chloe.bennett@utdallas.edu', 'ECSW 3.100', 'Computer Science'),
(22, 'Dr. Ivan Dimitrov', 9728840009, 'ivan.dimitrov@utdallas.edu', 'ECSN 2.105', 'Software Engineering'),
(23, 'Dr. Helena Brooks', 9728840010, 'helena.brooks@utdallas.edu', 'ECSS 1.222', 'Computer Science'),
(24, 'Dr. Victor Chang', 9728840011, 'victor.chang@utdallas.edu', 'ECSW 2.215', 'Software Engineering'),
(25, 'Dr. Maya Khan', 9728840012, 'maya.khan@utdallas.edu', 'ECSS 3.105', 'Computer Science'),
(26, 'Dr. Liam Johnson', 9728840013, 'liam.johnson@utdallas.edu', 'ECSN 1.215', 'Software Engineering'),
(27, 'Dr. Zoe Taylor', 9728840014, 'zoe.taylor@utdallas.edu', 'ECSW 3.100', 'Computer Science'),
(28, 'Dr. Daniel Cho', 9728840015, 'daniel.cho@utdallas.edu', 'ECSN 2.300', 'Software Engineering'),
(29, 'Dr. Grace Liu', 9728840016, 'grace.liu@utdallas.edu', 'ECSW 1.221', 'Computer Science'),
(30, 'Dr. Michael Torres', 9728840017, 'michael.torres@utdallas.edu', 'ECSS 2.200', 'Software Engineering');

-- --------------------------------------------------------

--
-- Table structure for table `prerequisite`
--

CREATE TABLE `prerequisite` (
  `course_id` int NOT NULL,
  `course_prerequisite` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `prerequisite`
--

INSERT INTO `prerequisite` (`course_id`, `course_prerequisite`) VALUES
(2, 1),
(3, 1),
(9, 1),
(3, 2),
(4, 2),
(7, 2),
(13, 2),
(14, 2),
(6, 3),
(10, 3),
(12, 3),
(17, 3),
(5, 4),
(11, 4),
(16, 4),
(6, 5),
(23, 6),
(8, 7),
(25, 8),
(28, 9),
(18, 11),
(20, 11),
(26, 11),
(15, 12),
(22, 13),
(19, 17),
(21, 18),
(24, 20),
(27, 24),
(29, 25);

-- --------------------------------------------------------

--
-- Table structure for table `rating`
--

CREATE TABLE `rating` (
  `rating_id` int NOT NULL,
  `instructor_id` int NOT NULL,
  `rating_number` tinyint NOT NULL,
  `rating_student_grade` enum('A+','A','A-','B+','B','B-','C+','C','C-','D+','D','D-','F') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `rating`
--

INSERT INTO `rating` (`rating_id`, `instructor_id`, `rating_number`, `rating_student_grade`) VALUES
(1, 1, 4, 'A'),
(2, 1, 5, 'A-'),
(3, 2, 3, 'B'),
(4, 2, 4, 'A'),
(5, 3, 4, 'B'),
(6, 3, 5, 'A'),
(7, 4, 2, 'C'),
(8, 4, 3, 'B-'),
(9, 5, 5, 'A'),
(10, 5, 4, 'A'),
(11, 6, 4, 'B'),
(12, 6, 5, 'B'),
(13, 7, 5, 'A-'),
(14, 7, 4, 'A'),
(15, 8, 3, 'B'),
(16, 8, 4, 'B+'),
(17, 9, 5, 'A'),
(18, 9, 4, 'A+'),
(19, 10, 4, 'B+'),
(20, 10, 3, 'C'),
(21, 11, 5, 'A'),
(22, 11, 4, 'B'),
(23, 12, 3, 'B'),
(24, 12, 5, 'A'),
(25, 13, 4, 'B'),
(26, 13, 5, 'B'),
(27, 14, 3, 'C-'),
(28, 14, 2, 'D+'),
(29, 15, 5, 'A-'),
(30, 15, 4, 'A-');

-- --------------------------------------------------------

--
-- Table structure for table `section`
--

CREATE TABLE `section` (
  `section_id` int NOT NULL,
  `course_id` int NOT NULL,
  `instructor_id` int NOT NULL,
  `location` varchar(255) DEFAULT 'UTD',
  `capacity` int DEFAULT NULL,
  `term` varchar(255) NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `days` varchar(10) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `section`
--

INSERT INTO `section` (`section_id`, `course_id`, `instructor_id`, `location`, `capacity`, `term`, `start_time`, `end_time`, `days`, `start_date`, `end_date`) VALUES
(1, 1, 1, 'ECSW 1.315', 100, 'Fall 2025', '09:00:00', '10:15:00', 'MW', '2025-08-25', '2025-12-05'),
(2, 2, 1, 'ECSS 2.415', 80, 'Fall 2025', '10:30:00', '11:45:00', 'TR', '2025-08-25', '2025-12-05'),
(3, 3, 3, 'ECSN 2.120', 60, 'Spring 2026', '14:00:00', '15:15:00', 'MWF', '2026-01-13', '2026-05-01'),
(4, 1, 2, 'ECSN 1.301', 95, 'Spring 2026', '12:30:00', '13:45:00', 'MW', '2026-01-13', '2026-05-01'),
(5, 2, 2, 'ECSN 1.210', 85, 'Spring 2026', '14:00:00', '15:15:00', 'TR', '2026-01-13', '2026-05-01'),
(6, 3, 3, 'ECSW 2.215', 60, 'Fall 2025', '10:00:00', '11:15:00', 'MW', '2025-08-25', '2025-12-05'),
(7, 4, 4, 'ECSS 3.420', 90, 'Fall 2025', '13:00:00', '14:15:00', 'TR', '2025-08-25', '2025-12-05'),
(8, 5, 5, 'ECSN 1.212', 70, 'Spring 2026', '09:30:00', '10:45:00', 'MWF', '2026-01-13', '2026-05-01'),
(9, 6, 6, 'ECSW 1.318', 50, 'Spring 2026', '11:00:00', '12:15:00', 'MW', '2026-01-13', '2026-05-01'),
(10, 4, 4, 'ECSW 2.111', 80, 'Spring 2026', '15:00:00', '16:15:00', 'TR', '2026-01-13', '2026-05-01'),
(11, 5, 5, 'ECSS 2.210', 65, 'Fall 2025', '16:00:00', '17:15:00', 'MW', '2025-08-25', '2025-12-05'),
(12, 7, 7, 'ECSN 2.100', 60, 'Fall 2025', '09:00:00', '10:15:00', 'TR', '2025-08-25', '2025-12-05'),
(13, 7, 7, 'ECSW 1.210', 75, 'Spring 2026', '13:00:00', '14:15:00', 'MW', '2026-01-13', '2026-05-01'),
(14, 8, 8, 'ECSN 2.210', 60, 'Spring 2026', '12:30:00', '13:45:00', 'MW', '2026-01-13', '2026-05-01'),
(15, 8, 8, 'ECSW 3.120', 50, 'Fall 2025', '11:00:00', '12:15:00', 'TR', '2025-08-25', '2025-12-05'),
(16, 9, 9, 'ECSW 1.222', 90, 'Fall 2025', '13:30:00', '14:45:00', 'MW', '2025-08-25', '2025-12-05'),
(17, 10, 10, 'ECSS 2.300', 55, 'Spring 2026', '08:30:00', '09:45:00', 'TR', '2026-01-13', '2026-05-01'),
(18, 11, 11, 'ECSW 3.300', 45, 'Fall 2025', '12:00:00', '13:15:00', 'MW', '2025-08-25', '2025-12-05'),
(19, 12, 12, 'ECSN 1.202', 60, 'Spring 2026', '10:30:00', '11:45:00', 'TR', '2026-01-13', '2026-05-01'),
(20, 12, 12, 'ECSN 1.202', 58, 'Fall 2025', '16:00:00', '17:15:00', 'MW', '2025-08-25', '2025-12-05'),
(21, 13, 13, 'ECSN 1.100', 80, 'Fall 2025', '14:00:00', '15:15:00', 'TR', '2025-08-25', '2025-12-05'),
(22, 14, 14, 'ECSW 2.102', 85, 'Spring 2026', '13:30:00', '14:45:00', 'MW', '2026-01-13', '2026-05-01'),
(23, 15, 15, 'ECSW 3.103', 60, 'Fall 2025', '08:30:00', '09:45:00', 'TR', '2025-08-25', '2025-12-05'),
(24, 16, 16, 'ECSS 2.111', 40, 'Spring 2026', '09:00:00', '10:15:00', 'F', '2026-01-13', '2026-05-01'),
(25, 17, 17, 'ECSN 1.215', 50, 'Fall 2025', '12:00:00', '13:15:00', 'TR', '2025-08-25', '2025-12-05'),
(26, 18, 18, 'ECSW 1.211', 75, 'Spring 2026', '10:30:00', '11:45:00', 'MW', '2026-01-13', '2026-05-01'),
(27, 19, 19, 'ECSS 3.210', 55, 'Fall 2025', '14:00:00', '15:15:00', 'MWF', '2025-08-25', '2025-12-05'),
(28, 20, 20, 'ECSN 2.201', 60, 'Spring 2026', '15:00:00', '16:15:00', 'TR', '2026-01-13', '2026-05-01'),
(29, 21, 21, 'ECSW 1.319', 65, 'Fall 2025', '09:30:00', '10:45:00', 'MW', '2025-08-25', '2025-12-05'),
(30, 22, 22, 'ECSS 2.312', 58, 'Spring 2026', '08:00:00', '09:15:00', 'TR', '2026-01-13', '2026-05-01'),
(31, 23, 23, 'ECSN 3.001', 45, 'Fall 2025', '11:00:00', '12:15:00', 'MW', '2025-08-25', '2025-12-05'),
(32, 24, 24, 'ECSW 2.208', 70, 'Spring 2026', '16:00:00', '17:15:00', 'MWF', '2026-01-13', '2026-05-01'),
(33, 25, 25, 'ECSN 1.312', 60, 'Fall 2025', '10:30:00', '11:45:00', 'TR', '2025-08-25', '2025-12-05'),
(34, 26, 26, 'ECSW 3.201', 85, 'Spring 2026', '13:00:00', '14:15:00', 'MW', '2026-01-13', '2026-05-01'),
(35, 27, 27, 'ECSS 3.313', 66, 'Fall 2025', '14:30:00', '15:45:00', 'TR', '2025-08-25', '2025-12-05'),
(36, 28, 28, 'ECSW 1.107', 55, 'Spring 2026', '11:00:00', '12:15:00', 'MW', '2026-01-13', '2026-05-01'),
(37, 29, 29, 'ECSS 1.204', 50, 'Fall 2025', '09:30:00', '10:45:00', 'MW', '2025-08-25', '2025-12-05'),
(38, 30, 30, 'ECSN 2.203', 60, 'Spring 2026', '15:00:00', '16:15:00', 'MW', '2026-01-13', '2026-05-01'),
(39, 14, 14, 'ECSN 2.210', 45, 'Fall 2025', '17:00:00', '18:15:00', 'TR', '2025-08-25', '2025-12-05'),
(40, 6, 6, 'ECSW 1.318', 50, 'Fall 2025', '11:00:00', '12:15:00', 'MW', '2025-08-25', '2025-12-05'),
(41, 11, 11, 'ECSW 2.319', 75, 'Spring 2026', '08:00:00', '09:15:00', 'TR', '2026-01-13', '2026-05-01'),
(42, 19, 19, 'ECSN 2.205', 65, 'Spring 2026', '16:00:00', '17:15:00', 'MW', '2026-01-13', '2026-05-01'),
(43, 20, 20, 'ECSN 1.304', 70, 'Fall 2025', '12:30:00', '13:45:00', 'TR', '2025-08-25', '2025-12-05'),
(44, 23, 23, 'ECSN 3.210', 40, 'Spring 2026', '10:00:00', '11:15:00', 'MWF', '2026-01-13', '2026-05-01'),
(45, 25, 25, 'ECSW 2.321', 55, 'Spring 2026', '08:30:00', '09:45:00', 'MW', '2026-01-13', '2026-05-01');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1, 'admin', 'admin123'),
(2, 'user1', 'pass1'),
(3, 'test', 'test123');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`course_id`);

--
-- Indexes for table `instructor`
--
ALTER TABLE `instructor`
  ADD PRIMARY KEY (`instructor_id`);

--
-- Indexes for table `prerequisite`
--
ALTER TABLE `prerequisite`
  ADD PRIMARY KEY (`course_id`,`course_prerequisite`),
  ADD KEY `course_prerequisite` (`course_prerequisite`);

--
-- Indexes for table `rating`
--
ALTER TABLE `rating`
  ADD PRIMARY KEY (`rating_id`),
  ADD KEY `instructor_id` (`instructor_id`);

--
-- Indexes for table `section`
--
ALTER TABLE `section`
  ADD PRIMARY KEY (`section_id`),
  ADD KEY `instructor_id` (`instructor_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `course`
--
ALTER TABLE `course`
  MODIFY `course_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `instructor`
--
ALTER TABLE `instructor`
  MODIFY `instructor_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `rating`
--
ALTER TABLE `rating`
  MODIFY `rating_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `section`
--
ALTER TABLE `section`
  MODIFY `section_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `prerequisite`
--
ALTER TABLE `prerequisite`
  ADD CONSTRAINT `prerequisite_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `prerequisite_ibfk_2` FOREIGN KEY (`course_prerequisite`) REFERENCES `course` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `rating`
--
ALTER TABLE `rating`
  ADD CONSTRAINT `rating_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `instructor` (`instructor_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `section`
--
ALTER TABLE `section`
  ADD CONSTRAINT `section_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `instructor` (`instructor_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `section_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`) ON DELETE RESTRICT ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
