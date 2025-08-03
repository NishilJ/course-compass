-- MySQL dump 10.13  Distrib 9.3.0, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: course_compass_test
-- ------------------------------------------------------
-- Server version	9.3.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `course`
--

DROP TABLE IF EXISTS `course`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `course` (
  `course_id` int NOT NULL,
  `course_prefix` varchar(10) NOT NULL,
  `course_credits` int NOT NULL,
  `course_subject` varchar(255) NOT NULL,
  `course_number` int NOT NULL,
  `course_title` varchar(255) NOT NULL,
  `course_description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course`
--

LOCK TABLES `course` WRITE;
/*!40000 ALTER TABLE `course` DISABLE KEYS */;
INSERT INTO `course` VALUES (101,'CS',3,'Computer Science',1337,'Introduction to Programming','An introduction to programming using structured languages; covers basic control structures, data types, and problem-solving techniques.'),(102,'CS',3,'Computer Science',3345,'Data Structures & Algorithms','\"Analysis of algorithms including time complexity and Big‑O notation; implementation of stacks, queues, trees (including B‑trees), heaps, hashing, and graph algorithms. Emphasizes design and implementation.\" '),(103,'SE',3,'Software Engineering',3306,'Software Project Management','Focuses on processes and tools for successful software project planning, scheduling, risk analysis, and quality assurance.'),(104,'CS',3,'Computer Science',4375,'Operating Systems','Study of modern operating system concepts including process management, scheduling, synchronization, memory management, file systems, and device drivers.'),(105,'CS',3,'Computer Science',4485,'Computer Networks','Teaches principles of data communications and networking: protocols, layering, switching, routing, and TCP/IP architecture.'),(106,'SE',3,'Software Engineering',4351,'Requirements Engineering','Covers elicitation, specification, and validation of software requirements; modeling techniques and managing stakeholder interactions.'),(107,'CS',3,'Computer Science',3380,'Database Systems','Introduction to relational database design, SQL querying, normalization, transactions, indexing, and basic database connectivity.'),(108,'SE',3,'Software Engineering',4381,'Software Testing','Principles and practices of software testing; test planning, test case design, fault analysis, and automated testing tools.'),(109,'CS',3,'Computer Science',4301,'Programming Languages','Explores design concepts of modern programming languages; syntax, semantics, paradigms, type systems, and language translation.'),(110,'SE',3,'Software Engineering',4365,'Agile Software Development','Covers agile methodologies such as Scrum and Kanban; emphasis on iterative development, user stories, and continuous integration.'),(111,'CS',3,'Computer Science',4390,'Artificial Intelligence','Survey of foundational AI topics: search, knowledge representation, machine learning basics, reasoning, and planning techniques.'),(112,'SE',3,'Software Engineering',4305,'Object-Oriented Design','Design of object-oriented software using UML; covers design patterns, abstractions, and modular system architecture.'),(113,'CS',3,'Computer Science',4310,'Computer Architecture','Examines computer organization: instruction sets, pipelining, memory hierarchy, and performance metrics.'),(114,'CS',3,'Computer Science',4330,'Mobile App Development','Design and development of mobile applications for Android and iOS; covers UI frameworks, APIs, and deployment.'),(115,'SE',3,'Software Engineering',4320,'Software Design Patterns','Study of object-oriented design patterns such as Singleton, Factory, Observer and how to apply them in real-world software.'),(116,'CS',1,'Computer Science',4328,'Operating Systems Lab','Laboratory course accompanying OS theory; covers system calls, kernel modules, process control, and concurrency exercises.'),(117,'SE',3,'Software Engineering',4345,'Human-Centered Design','Human-centered design methodology; user research, prototyping, usability testing, and iterative interface design.'),(118,'CS',3,'Computer Science',4315,'Machine Learning','Introduction to supervised and unsupervised learning, models including regression, decision trees, clustering, and evaluation metrics.'),(119,'SE',3,'Software Engineering',4361,'DevOps','Principles and practices of DevOps: continuous integration, continuous deployment, infrastructure as code, and monitoring.'),(120,'CS',3,'Computer Science',4380,'Computer Graphics','Fundamentals of rendering, modeling, transformation, shading, and animation; includes real-time graphics techniques.'),(121,'SE',3,'Software Engineering',4391,'Cloud Computing','Concepts and architecture of cloud platforms, virtualization, distributed systems, microservices, and orchestration.'),(122,'CS',3,'Computer Science',4309,'Parallel Computing','Principles of parallel algorithms, multi-core processing, synchronization, performance metrics, and programming models.'),(123,'SE',3,'Software Engineering',4355,'Engineering Ethics','Study of ethical issues in computing professions including privacy, intellectual property, and social impact.'),(124,'CS',3,'Computer Science',4378,'Cybersecurity Principles','Cybersecurity foundations; covers threat modeling, encryption, authentication, network security, and incident response.'),(125,'SE',3,'Software Engineering',4382,'Advanced Testing','Advanced software testing strategies: automated testing frameworks, fuzz testing, performance and security testing.'),(126,'CS',3,'Computer Science',4386,'Big Data Analytics','Analyzing large-scale data systems; covers data processing pipelines, analytics frameworks, and visualization.'),(127,'SE',3,'Software Engineering',4373,'Secure Software Engineering','Secure design principles, threat mitigation, code reviews, and security validation in software lifecycle.'),(128,'CS',3,'Computer Science',4306,'Compiler Design','Techniques for designing compilers: lexical analysis, parsing, semantic analysis, optimization, and code generation.'),(129,'SE',3,'Software Engineering',4393,'Continuous Integration','Implementation of CI pipelines, automated building, testing infrastructure, and version control practices.'),(130,'CS',3,'Computer Science',4326,'Digital Forensics','Principles of digital investigation: crime scene data collection, analysis tools, network forensics, and evidence preservation.');
/*!40000 ALTER TABLE `course` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `instructor`
--

DROP TABLE IF EXISTS `instructor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `instructor` (
  `instructor_id` int NOT NULL,
  `instructor_name` varchar(255) NOT NULL,
  `instructor_phone` bigint DEFAULT NULL,
  `instructor_email` varchar(255) DEFAULT NULL,
  `instructor_office` varchar(255) DEFAULT NULL,
  `instructor_dep` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`instructor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `instructor`
--

LOCK TABLES `instructor` WRITE;
/*!40000 ALTER TABLE `instructor` DISABLE KEYS */;
INSERT INTO `instructor` VALUES (1,'Dr. Jane Smith',9728831234,'jane.smith@utdallas.edu','ECSW 3.210','Computer Science'),(2,'Dr. Alan Lee',9728835678,'alan.lee@utdallas.edu','ECSS 2.312','Software Engineering'),(3,'Dr. Emily Johnson',9728834321,'emily.johnson@utdallas.edu','ECSS 3.420','Computer Science'),(4,'Dr. Robert Kim',9728838765,'robert.kim@utdallas.edu','ECSW 2.110','Software Engineering'),(5,'Dr. Olivia Chen',9728831111,'olivia.chen@utdallas.edu','ECSS 3.100','Computer Science'),(6,'Dr. Mark Patel',9728832222,'mark.patel@utdallas.edu','ECSN 1.321','Software Engineering'),(7,'Dr. Sarah Park',9728833333,'sarah.park@utdallas.edu','ECSS 3.210','Computer Science'),(8,'Dr. Kevin White',9728834444,'kevin.white@utdallas.edu','ECSN 2.100','Software Engineering'),(9,'Dr. Rachel Adams',9728835555,'rachel.adams@utdallas.edu','ECSW 1.215','Computer Science'),(10,'Dr. Henry Zhou',9728836666,'henry.zhou@utdallas.edu','ECSS 2.330','Software Engineering'),(11,'Dr. Anita Rao',9728837777,'anita.rao@utdallas.edu','ECSN 3.120','Computer Science'),(12,'Dr. Brian Wong',9728838888,'brian.wong@utdallas.edu','ECSS 1.110','Software Engineering'),(13,'Dr. Laura Kim',9728839999,'laura.kim@utdallas.edu','ECSW 2.112','Computer Science'),(14,'Dr. James Lee',9728840001,'james.lee@utdallas.edu','ECSS 3.331','Software Engineering'),(15,'Dr. Priya Shah',9728840002,'priya.shah@utdallas.edu','ECSN 1.101','Computer Science'),(16,'Dr. Tom Nguyen',9728840003,'tom.nguyen@utdallas.edu','ECSW 3.213','Software Engineering'),(17,'Dr. Amy Patel',9728840004,'amy.patel@utdallas.edu','ECSW 1.201','Computer Science'),(18,'Dr. Jason Reed',9728840005,'jason.reed@utdallas.edu','ECSS 2.101','Software Engineering'),(19,'Dr. Nina Gupta',9728840006,'nina.gupta@utdallas.edu','ECSN 2.114','Computer Science'),(20,'Dr. Omar Sanchez',9728840007,'omar.sanchez@utdallas.edu','ECSN 3.001','Software Engineering'),(21,'Dr. Chloe Bennett',9728840008,'chloe.bennett@utdallas.edu','ECSW 3.100','Computer Science'),(22,'Dr. Ivan Dimitrov',9728840009,'ivan.dimitrov@utdallas.edu','ECSN 2.105','Software Engineering'),(23,'Dr. Helena Brooks',9728840010,'helena.brooks@utdallas.edu','ECSS 1.222','Computer Science'),(24,'Dr. Victor Chang',9728840011,'victor.chang@utdallas.edu','ECSW 2.215','Software Engineering'),(25,'Dr. Maya Khan',9728840012,'maya.khan@utdallas.edu','ECSS 3.105','Computer Science'),(26,'Dr. Liam Johnson',9728840013,'liam.johnson@utdallas.edu','ECSN 1.215','Software Engineering'),(27,'Dr. Zoe Taylor',9728840014,'zoe.taylor@utdallas.edu','ECSW 3.100','Computer Science'),(28,'Dr. Daniel Cho',9728840015,'daniel.cho@utdallas.edu','ECSN 2.300','Software Engineering'),(29,'Dr. Grace Liu',9728840016,'grace.liu@utdallas.edu','ECSW 1.221','Computer Science'),(30,'Dr. Michael Torres',9728840017,'michael.torres@utdallas.edu','ECSS 2.200','Software Engineering');
/*!40000 ALTER TABLE `instructor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prerequisite`
--

DROP TABLE IF EXISTS `prerequisite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prerequisite` (
  `course_id` int DEFAULT NULL,
  `course_prerequisite` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prerequisite`
--

LOCK TABLES `prerequisite` WRITE;
/*!40000 ALTER TABLE `prerequisite` DISABLE KEYS */;
INSERT INTO `prerequisite` VALUES (102,101),(103,102),(104,102),(105,104),(106,105),(107,102),(108,107),(109,101),(110,103),(111,104),(112,103),(113,102),(114,102),(115,112),(116,104),(117,103),(118,111),(119,117),(120,111),(121,118),(122,113),(123,106),(129,125),(127,124),(128,109),(124,120),(126,111),(125,108);
/*!40000 ALTER TABLE `prerequisite` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rating`
--

DROP TABLE IF EXISTS `rating`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rating` (
  `rating_id` int NOT NULL,
  `instructor_id` int NOT NULL,
  `rating_number` tinyint NOT NULL,
  `rating_student_grade` varchar(2) DEFAULT NULL,
  PRIMARY KEY (`rating_id`),
  KEY `instructor_id` (`instructor_id`),
  CONSTRAINT `rating_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `instructor` (`instructor_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rating`
--

LOCK TABLES `rating` WRITE;
/*!40000 ALTER TABLE `rating` DISABLE KEYS */;
INSERT INTO `rating` VALUES (1,1,4,'A'),(2,1,5,'A'),(3,2,3,'B'),(4,2,4,''),(5,3,4,'B'),(6,3,5,'A'),(7,4,2,'C'),(8,4,3,'B'),(9,5,5,'A'),(10,5,4,'A'),(11,6,4,'B'),(12,6,5,'B'),(13,7,5,'A'),(14,7,4,'A'),(15,8,3,'B'),(16,8,4,'B'),(17,9,5,'A'),(18,9,4,'A'),(19,10,4,'B'),(20,10,3,'C'),(21,11,5,'A'),(22,11,4,'B'),(23,12,3,'B'),(24,12,5,'A'),(25,13,4,'B'),(26,13,5,'B'),(27,14,3,'C'),(28,14,2,'B'),(29,15,5,'A'),(30,15,4,'A');
/*!40000 ALTER TABLE `rating` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `section`
--

DROP TABLE IF EXISTS `section`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
  `end_date` date DEFAULT NULL,
  PRIMARY KEY (`section_id`),
  KEY `instructor_id` (`instructor_id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `section_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `instructor` (`instructor_id`) ON DELETE RESTRICT,
  CONSTRAINT `section_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `section`
--

LOCK TABLES `section` WRITE;
/*!40000 ALTER TABLE `section` DISABLE KEYS */;
INSERT INTO `section` VALUES (1,101,1,'ECSW 1.315',100,'Fall 2025','09:00:00','10:15:00','MW','2025-08-25','2025-12-05'),(2,102,2,'ECSS 2.415',80,'Fall 2025','10:30:00','11:45:00','TR','2025-08-25','2025-12-05'),(3,103,3,'ECSN 2.120',60,'Spring 2026','14:00:00','15:15:00','MWF','2026-01-13','2026-05-01'),(4,101,1,'ECSN 1.301',95,'Spring 2026','12:30:00','13:45:00','MW','2026-01-13','2026-05-01'),(5,102,2,'ECSN 1.210',85,'Spring 2026','14:00:00','15:15:00','TR','2026-01-13','2026-05-01'),(6,103,3,'ECSW 2.215',60,'Fall 2025','10:00:00','11:15:00','MW','2025-08-25','2025-12-05'),(7,104,4,'ECSS 3.420',90,'Fall 2025','13:00:00','14:15:00','TR','2025-08-25','2025-12-05'),(8,105,5,'ECSN 1.212',70,'Spring 2026','09:30:00','10:45:00','MWF','2026-01-13','2026-05-01'),(9,106,6,'ECSW 1.318',50,'Spring 2026','11:00:00','12:15:00','MW','2026-01-13','2026-05-01'),(10,104,4,'ECSW 2.111',80,'Spring 2026','15:00:00','16:15:00','TR','2026-01-13','2026-05-01'),(11,105,5,'ECSS 2.210',65,'Fall 2025','16:00:00','17:15:00','MW','2025-08-25','2025-12-05'),(12,107,7,'ECSN 2.100',60,'Fall 2025','09:00:00','10:15:00','TR','2025-08-25','2025-12-05'),(13,107,7,'ECSW 1.210',75,'Spring 2026','13:00:00','14:15:00','MW','2026-01-13','2026-05-01'),(14,108,8,'ECSN 2.210',60,'Spring 2026','12:30:00','13:45:00','MW','2026-01-13','2026-05-01'),(15,108,8,'ECSW 3.120',50,'Fall 2025','11:00:00','12:15:00','TR','2025-08-25','2025-12-05'),(16,109,9,'ECSW 1.222',90,'Fall 2025','13:30:00','14:45:00','MW','2025-08-25','2025-12-05'),(17,110,10,'ECSS 2.300',55,'Spring 2026','08:30:00','09:45:00','TR','2026-01-13','2026-05-01'),(18,111,11,'ECSW 3.300',45,'Fall 2025','12:00:00','13:15:00','MW','2025-08-25','2025-12-05'),(19,112,12,'ECSN 1.202',60,'Spring 2026','10:30:00','11:45:00','TR','2026-01-13','2026-05-01'),(20,112,12,'ECSN 1.202',58,'Fall 2025','16:00:00','17:15:00','MW','2025-08-25','2025-12-05'),(21,113,13,'ECSN 1.100',80,'Fall 2025','14:00:00','15:15:00','TR','2025-08-25','2025-12-05'),(22,114,14,'ECSW 2.102',85,'Spring 2026','13:30:00','14:45:00','MW','2026-01-13','2026-05-01'),(23,115,15,'ECSW 3.103',60,'Fall 2025','08:30:00','09:45:00','TR','2025-08-25','2025-12-05'),(24,116,16,'ECSS 2.111',40,'Spring 2026','09:00:00','10:15:00','F','2026-01-13','2026-05-01'),(25,117,17,'ECSN 1.215',50,'Fall 2025','12:00:00','13:15:00','TR','2025-08-25','2025-12-05'),(26,118,18,'ECSW 1.211',75,'Spring 2026','10:30:00','11:45:00','MW','2026-01-13','2026-05-01'),(27,119,19,'ECSS 3.210',55,'Fall 2025','14:00:00','15:15:00','MWF','2025-08-25','2025-12-05'),(28,120,20,'ECSN 2.201',60,'Spring 2026','15:00:00','16:15:00','TR','2026-01-13','2026-05-01'),(29,121,21,'ECSW 1.319',65,'Fall 2025','09:30:00','10:45:00','MW','2025-08-25','2025-12-05'),(30,122,22,'ECSS 2.312',58,'Spring 2026','08:00:00','09:15:00','TR','2026-01-13','2026-05-01'),(31,123,23,'ECSN 3.001',45,'Fall 2025','11:00:00','12:15:00','MW','2025-08-25','2025-12-05'),(32,124,24,'ECSW 2.208',70,'Spring 2026','16:00:00','17:15:00','MWF','2026-01-13','2026-05-01'),(33,125,25,'ECSN 1.312',60,'Fall 2025','10:30:00','11:45:00','TR','2025-08-25','2025-12-05'),(34,126,26,'ECSW 3.201',85,'Spring 2026','13:00:00','14:15:00','MW','2026-01-13','2026-05-01'),(35,127,27,'ECSS 3.313',66,'Fall 2025','14:30:00','15:45:00','TR','2025-08-25','2025-12-05'),(36,128,28,'ECSW 1.107',55,'Spring 2026','11:00:00','12:15:00','MW','2026-01-13','2026-05-01'),(37,129,29,'ECSS 1.204',50,'Fall 2025','09:30:00','10:45:00','MW','2025-08-25','2025-12-05'),(38,130,30,'ECSN 2.203',60,'Spring 2026','15:00:00','16:15:00','MW','2026-01-13','2026-05-01'),(39,114,14,'ECSN 2.210',45,'Fall 2025','17:00:00','18:15:00','TR','2025-08-25','2025-12-05'),(40,106,6,'ECSW 1.318',50,'Fall 2025','11:00:00','12:15:00','MW','2025-08-25','2025-12-05'),(41,111,11,'ECSW 2.319',75,'Spring 2026','08:00:00','09:15:00','TR','2026-01-13','2026-05-01'),(42,119,19,'ECSN 2.205',65,'Spring 2026','16:00:00','17:15:00','MW','2026-01-13','2026-05-01'),(43,120,20,'ECSN 1.304',70,'Fall 2025','12:30:00','13:45:00','TR','2025-08-25','2025-12-05'),(44,123,23,'ECSN 3.210',40,'Spring 2026','10:00:00','11:15:00','MWF','2026-01-13','2026-05-01'),(45,125,25,'ECSW 2.321',55,'Spring 2026','08:30:00','09:45:00','MW','2026-01-13','2026-05-01');
/*!40000 ALTER TABLE `section` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-08-02 22:46:57
