CREATE TABLE course (
    course_id INT NOT NULL AUTO_INCREMENT,
    course_prefix VARCHAR(10) NOT NULL,
    course_credits INT NOT NULL,
    course_subject VARCHAR(255) NOT NULL,
    course_number INT NOT NULL,
    course_title VARCHAR (255) NOT NULL,
    course_description TEXT DEFAULT NULL,
    PRIMARY KEY (course_id)
);

CREATE TABLE instructor (
    instructor_id INT NOT NULL AUTO_INCREMENT,
    instructor_name VARCHAR(255) NOT NULL,
    instructor_phone BIGINT DEFAULT NULL,
    instructor_email VARCHAR(255) DEFAULT NULL,
    instructor_office VARCHAR(255) DEFAULT NULL,
    instructor_dep VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (instructor_id)
);

CREATE TABLE section (
    section_id INT NOT NULL AUTO_INCREMENT,
    course_id INT NOT NULL,
    instructor_id INT NOT NULL,
    location VARCHAR(255) DEFAULT 'UTD',
    capacity INT DEFAULT NULL,
    term VARCHAR(255) NOT NULL,
    start_time TIME DEFAULT NULL,
    end_time TIME DEFAULT NULL,
    days VARCHAR(10) DEFAULT NULL,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    PRIMARY KEY (section_id),
    FOREIGN KEY (instructor_id) REFERENCES instructor (instructor_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    FOREIGN KEY (course_id) REFERENCES course (course_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

CREATE TABLE prerequisite (
    course_id INT NOT NULL,
    course_prerequisite INT NOT NULL,
    PRIMARY KEY (course_id, course_prerequisite),
    FOREIGN KEY (course_id) REFERENCES course (course_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (course_prerequisite) REFERENCES course (course_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

CREATE TABLE rating (
    rating_id INT NOT NULL AUTO_INCREMENT,
    instructor_id INT NOT NULL,
    rating_number TINYINT NOT NULL,
    rating_student_grade ENUM('A+', 'A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D+', 'D', 'D-', 'F') DEFAULT NULL,
    PRIMARY KEY (rating_id),
    FOREIGN KEY (instructor_id) REFERENCES instructor (instructor_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL
);

