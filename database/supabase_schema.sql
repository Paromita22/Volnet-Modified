-- ==========================================================
-- VolNet - Supabase (PostgreSQL) Database Schema
-- Run this in Supabase SQL Editor to initialize all tables
-- ==========================================================

-- 1. Users Table
CREATE TABLE IF NOT EXISTS users (
    user_id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL CHECK (role IN ('volunteer', 'organization', 'admin')),
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- 2. Volunteer Profile Table
CREATE TABLE IF NOT EXISTS volunteer (
    volunteer_id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    name VARCHAR(100),
    age INT,
    gender VARCHAR(10),
    mobile VARCHAR(20),
    address TEXT,
    skills TEXT,
    bio TEXT,
    profile_pic_path VARCHAR(255)
);

-- 3. Organization Profile Table
CREATE TABLE IF NOT EXISTS organization (
    org_id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    org_name VARCHAR(150),
    profile_pic_path VARCHAR(255),
    contact_email VARCHAR(100),
    description TEXT,
    bio TEXT,
    mobile VARCHAR(20),
    address TEXT
);

-- 4. Admin Profile Table
CREATE TABLE IF NOT EXISTS admin (
    admin_id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    full_name VARCHAR(100),
    privileges TEXT,
    mobile VARCHAR(20)
);

-- 5. Events Table
CREATE TABLE IF NOT EXISTS eventt (
    id SERIAL PRIMARY KEY,
    org_id INT REFERENCES organization(org_id) ON DELETE SET NULL,
    title VARCHAR(255) NOT NULL,
    type VARCHAR(100) NOT NULL,
    start_date DATE,
    start_time TIME,
    end_date DATE,
    end_time TIME,
    location VARCHAR(255),
    description TEXT,
    volunteers INT DEFAULT 0,
    age_restriction VARCHAR(50),
    gender VARCHAR(20) DEFAULT 'Both',
    skills TEXT,
    contact VARCHAR(100),
    deadline DATE,
    is_urgent SMALLINT DEFAULT 0,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- 6. Applications Table
CREATE TABLE IF NOT EXISTS applications (
    application_id SERIAL PRIMARY KEY,
    event_id INT NOT NULL REFERENCES eventt(id) ON DELETE CASCADE,
    volunteer_id INT NOT NULL REFERENCES volunteer(volunteer_id) ON DELETE CASCADE,
    org_id INT REFERENCES organization(org_id) ON DELETE CASCADE,
    status VARCHAR(50) NOT NULL DEFAULT 'Pending' CHECK (status IN ('Pending', 'Accepted', 'Rejected')),
    application_date TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unique_application UNIQUE (event_id, volunteer_id)
);

-- 7. Volunteer Events (Attendance Tracking)
CREATE TABLE IF NOT EXISTS volunteer_events (
    id SERIAL PRIMARY KEY,
    volunteer_id INT NOT NULL REFERENCES volunteer(volunteer_id) ON DELETE CASCADE,
    event_id INT NOT NULL REFERENCES eventt(id) ON DELETE CASCADE,
    status VARCHAR(50) DEFAULT 'Registered',
    hours_completed NUMERIC(5, 2),
    attendance_date DATE,
    attended_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- 8. Certificates Table
CREATE TABLE IF NOT EXISTS certificates (
    certificate_id SERIAL PRIMARY KEY,
    volunteer_id INT NOT NULL REFERENCES volunteer(volunteer_id) ON DELETE CASCADE,
    event_id INT NOT NULL REFERENCES eventt(id) ON DELETE CASCADE,
    file_path VARCHAR(255) NOT NULL,
    generated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unique_cert_per_event UNIQUE (volunteer_id, event_id)
);

-- 9. Reviews Table
CREATE TABLE IF NOT EXISTS reviews (
    review_id SERIAL PRIMARY KEY,
    reviewer_user_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    reviewer_role VARCHAR(50) NOT NULL,
    reviewee_user_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    reviewee_role VARCHAR(50) NOT NULL,
    review_text TEXT NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- 10. Complaints Table
CREATE TABLE IF NOT EXISTS complaints (
    complaint_id SERIAL PRIMARY KEY,
    complainant_user_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    complainant_role VARCHAR(50) NOT NULL,
    accused_user_id INT REFERENCES users(user_id) ON DELETE CASCADE,
    accused_role VARCHAR(50),
    complaint_text TEXT NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Pending',
    complaint_date TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- 11. Blogs Table
CREATE TABLE IF NOT EXISTS blogs (
    blog_id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    image_path VARCHAR(255),
    content TEXT NOT NULL,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- 12. Organization Past Events Table
CREATE TABLE IF NOT EXISTS org_past_events (
    id SERIAL PRIMARY KEY,
    org_id INT NOT NULL REFERENCES organization(org_id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================================
-- Performance Indexes
-- ==========================================================
CREATE INDEX IF NOT EXISTS idx_volunteer_user ON volunteer(user_id);
CREATE INDEX IF NOT EXISTS idx_organization_user ON organization(user_id);
CREATE INDEX IF NOT EXISTS idx_eventt_org ON eventt(org_id);
CREATE INDEX IF NOT EXISTS idx_applications_event ON applications(event_id);
CREATE INDEX IF NOT EXISTS idx_applications_volunteer ON applications(volunteer_id);
CREATE INDEX IF NOT EXISTS idx_blogs_user ON blogs(user_id);
