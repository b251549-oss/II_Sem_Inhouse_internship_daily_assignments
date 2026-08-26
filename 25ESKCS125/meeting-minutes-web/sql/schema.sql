-- AI Meeting Minutes Summarizer - Full Schema (with Auth)
CREATE DATABASE IF NOT EXISTS meeting_minutes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE meeting_minutes;

-- Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    api_key VARCHAR(255) DEFAULT NULL,          -- for live AI (OpenAI-compatible)
    api_base VARCHAR(255) DEFAULT 'https://api.openai.com/v1',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Meetings (now linked to user)
CREATE TABLE IF NOT EXISTS meetings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    meeting_date DATE,
    location VARCHAR(150) DEFAULT NULL,
    organizer VARCHAR(100) DEFAULT NULL,
    attendees TEXT,
    executive_summary TEXT,
    discussion_points TEXT,
    decisions TEXT,
    open_questions TEXT,
    next_steps TEXT,
    raw_notes LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Action Items
CREATE TABLE IF NOT EXISTS action_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id INT,
    action_text VARCHAR(500) NOT NULL,
    owner VARCHAR(100) DEFAULT 'Unassigned',
    deadline DATE DEFAULT NULL,
    priority ENUM('High', 'Medium', 'Low') DEFAULT 'Medium',
    status ENUM('Open', 'In Progress', 'Done', 'Cancelled') DEFAULT 'Open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Default demo user (password: password123)
INSERT INTO users (name, email, password) VALUES
('Demo User', 'demo@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
