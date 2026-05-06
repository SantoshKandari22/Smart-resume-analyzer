CREATE DATABASE IF NOT EXISTS job_matcher;
USE job_matcher;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS resumes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,  -- One resume per user (latest upload replaces old)
    file_path VARCHAR(255) NOT NULL,
    extracted_text LONGTEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS user_skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    skill VARCHAR(50) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_skill (user_id, skill)
);

CREATE TABLE IF NOT EXISTS jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    required_skills TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample jobs
INSERT INTO jobs (title, description, required_skills) VALUES 
('PHP Developer', 'Looking for a skilled backend developer proficient in PHP and MySQL for building scalable web applications.', 'PHP,SQL,HTML,CSS'),
('Frontend Developer', 'Modern frontend developer needed for crafting beautiful, responsive UI with JavaScript and CSS frameworks.', 'JavaScript,HTML,CSS'),
('Full Stack Engineer', 'End-to-end developer handling both backend APIs and modern frontend interfaces.', 'PHP,JavaScript,SQL,HTML,CSS'),
('Database Administrator', 'Manage, tune, and optimize relational SQL databases for enterprise applications.', 'SQL,PHP'),
('Web Designer', 'Creative designer focused on UI/UX, wireframing, and responsive styling.', 'HTML,CSS,JavaScript'),
('Laravel Developer', 'Build REST APIs and web apps using the Laravel PHP framework with MySQL.', 'PHP,Laravel,SQL,HTML,CSS'),
('React Developer', 'Develop high-performance SPAs with React, Redux, and modern JS tooling.', 'JavaScript,React,HTML,CSS'),
('Python Backend Engineer', 'Design scalable server-side solutions using Python and SQL databases.', 'Python,SQL'),
('Vue.js Developer', 'Build interactive UIs using Vue.js and integrate with PHP or Node.js backends.', 'Vue.js,JavaScript,HTML,CSS'),
('Java Software Engineer', 'Develop enterprise-grade applications and microservices using Java.', 'Java,SQL');
