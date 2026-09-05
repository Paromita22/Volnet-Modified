
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('volunteer', 'organization', 'admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE volunteer (
    volunteer_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100),
    age INT,
    skills TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);


CREATE TABLE organization (
    org_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    org_name VARCHAR(150),
    contact_email VARCHAR(100),
    description TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);


CREATE TABLE admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    full_name VARCHAR(100),
    privileges TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);



ALTER TABLE users ADD username VARCHAR(50) UNIQUE;

ALTER TABLE volunteer
ADD gender VARCHAR(10),
ADD mobile VARCHAR(20),
ADD address TEXT;

ALTER TABLE organization
ADD mobile VARCHAR(20),
ADD address TEXT;

ALTER TABLE admin
ADD mobile VARCHAR(20);





CREATE table eventt (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Basic Details
    title VARCHAR(255) NOT NULL,
    type ENUM('Animal Care', 'climate Action', 'Elderly Care', 'Women Empowerment', 'Child Care', 'Special Needs Care') NOT NULL,
    start_date DATE,
    start_time TIME,
    end_date DATE,
    end_time TIME,
    location VARCHAR(255),
    description TEXT,
    
    -- Volunteer Requirements
    volunteers INT,
    age_restriction VARCHAR(50),
    gender ENUM('Both', 'Male', 'Female'),
    skills TEXT,
    contact VARCHAR(100),
    deadline DATE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);











ALTER TABLE volunteer ADD bio TEXT NULL;
CREATE TABLE volunteer_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    volunteer_id INT NOT NULL,
    event_id INT NOT NULL,
    status ENUM('Registered', 'Attended', 'Cancelled') DEFAULT 'Registered',
    hours_completed DECIMAL(5, 2), -- e.g., 4.50 hours
    attended_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (volunteer_id) REFERENCES volunteer(volunteer_id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES eventt(id) ON DELETE CASCADE
);


ALTER TABLE eventt ADD org_id INT NULL;


CREATE TABLE applications (
    application_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    volunteer_id INT NOT NULL,
    org_id INT NOT NULL,
    status ENUM('Pending', 'Accepted', 'Rejected') NOT NULL DEFAULT 'Pending',
    application_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES eventt(id) ON DELETE CASCADE,
    FOREIGN KEY (volunteer_id) REFERENCES volunteer(volunteer_id) ON DELETE CASCADE,
    FOREIGN KEY (org_id) REFERENCES organization(org_id) ON DELETE CASCADE,
    -- This ensures a volunteer can only apply ONCE to any given event
    UNIQUE KEY unique_application (event_id, volunteer_id)
);


UPDATE eventt
SET org_id = 3
WHERE id = 1;









-- 1. Add new columns to the organization table
ALTER TABLE organization
ADD bio TEXT NULL AFTER description,
ADD profile_pic_path VARCHAR(255) NULL AFTER org_name;

-- 2. Create a new table for the organization's past events portfolio
CREATE TABLE org_past_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    org_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (org_id) REFERENCES organization(org_id) ON DELETE CASCADE
);



ALTER TABLE volunteer ADD COLUMN profile_pic_path VARCHAR(255) NULL;



ALTER TABLE eventt ADD is_urgent TINYINT(1) DEFAULT 0;




CREATE TABLE reviews (
  review_id INT AUTO_INCREMENT PRIMARY KEY,
  reviewer_user_id INT NOT NULL,
  reviewer_role ENUM('volunteer', 'organization', 'admin') NOT NULL,
  reviewee_user_id INT NOT NULL,
  reviewee_role ENUM('volunteer', 'organization', 'admin') NOT NULL,
  review_text TEXT NOT NULL,
  rating INT CHECK (rating >= 1 AND rating <= 5),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (reviewer_user_id) REFERENCES users(user_id),
  FOREIGN KEY (reviewee_user_id) REFERENCES users(user_id)
);



ALTER TABLE volunteer_events
CHANGE COLUMN attended_at attendance_date DATE NULL;


CREATE TABLE certificates (
    certificate_id INT AUTO_INCREMENT PRIMARY KEY,
    volunteer_id INT NOT NULL,
    event_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (volunteer_id) REFERENCES volunteer(volunteer_id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES eventt(id) ON DELETE CASCADE,
    -- A volunteer should only get one certificate per event
    UNIQUE KEY unique_cert_per_event (volunteer_id, event_id)
);

CREATE TABLE complaints (
    complaint_id INT AUTO_INCREMENT PRIMARY KEY,
    complainant_user_id INT NOT NULL,
    complainant_role ENUM('volunteer', 'organization', 'admin') NOT NULL,
    accused_user_id INT NOT NULL,
    accused_role ENUM('volunteer', 'organization', 'admin') NOT NULL,
    complaint_text TEXT NOT NULL,
    status ENUM('Pending', 'In Review', 'Resolved', 'Dismissed') NOT NULL DEFAULT 'Pending',
    complaint_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (complainant_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (accused_user_id) REFERENCES users(user_id) ON DELETE CASCADE
);


CREATE TABLE blogs (
    blog_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255),
    image_path VARCHAR(255),
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);