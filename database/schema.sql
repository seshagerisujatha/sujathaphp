
-- Leave Management System Database Schema
-- Drop database if exists and create new one
DROP DATABASE IF EXISTS leavedb;
CREATE DATABASE leavedb;
USE leavedb;

-- Companies table
CREATE TABLE tblcompany (
    COMPANYID int(11) NOT NULL AUTO_INCREMENT,
    COMPANYNAME varchar(90) NOT NULL,
    COMPANYADDRESS text NOT NULL,
    COMPANYCONTACTNO varchar(30) NOT NULL,
    CREATED_AT timestamp DEFAULT CURRENT_TIMESTAMP,
    UPDATED_AT timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (COMPANYID)
);

-- Departments table
CREATE TABLE tbldepartment (
    DEPARTMENTID int(11) NOT NULL AUTO_INCREMENT,
    DEPARTMENT varchar(80) NOT NULL,
    COMPANYID int(11) NOT NULL,
    CREATED_AT timestamp DEFAULT CURRENT_TIMESTAMP,
    UPDATED_AT timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (DEPARTMENTID),
    FOREIGN KEY (COMPANYID) REFERENCES tblcompany(COMPANYID) ON DELETE CASCADE
);

-- Leave types table
CREATE TABLE tblleavetype (
    LEAVETYPEID int(11) NOT NULL AUTO_INCREMENT,
    LEAVETYPE varchar(50) NOT NULL,
    LEAVEDAYS int(11) NOT NULL DEFAULT 0,
    DESCRIPTION text,
    CREATED_AT timestamp DEFAULT CURRENT_TIMESTAMP,
    UPDATED_AT timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (LEAVETYPEID)
);

-- Employees table
CREATE TABLE tblemployee (
    EMPID int(11) NOT NULL AUTO_INCREMENT,
    EMPLOYID varchar(30) NOT NULL UNIQUE,
    FNAME varchar(40) NOT NULL,
    LNAME varchar(40) NOT NULL,
    MNAME varchar(40),
    ADDRESS text NOT NULL,
    EMAIL varchar(90) NOT NULL UNIQUE,
    PHONE varchar(20),
    COMPANYID int(11) NOT NULL,
    DEPARTMENTID int(11) NOT NULL,
    POSITION varchar(50),
    DATEHIRED date,
    USERNAME varchar(30) NOT NULL UNIQUE,
    PASS varchar(255) NOT NULL,
    TYPE varchar(20) DEFAULT 'Employee',
    STATUS varchar(20) DEFAULT 'Active',
    CREATED_AT timestamp DEFAULT CURRENT_TIMESTAMP,
    UPDATED_AT timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (EMPID),
    FOREIGN KEY (COMPANYID) REFERENCES tblcompany(COMPANYID),
    FOREIGN KEY (DEPARTMENTID) REFERENCES tbldepartment(DEPARTMENTID)
);

-- Leave requests table
CREATE TABLE tblleave (
    LEAVEID int(11) NOT NULL AUTO_INCREMENT,
    EMPID int(11) NOT NULL,
    LEAVETYPEID int(11) NOT NULL,
    STARTDATE date NOT NULL,
    ENDDATE date NOT NULL,
    DAYS int(11) NOT NULL,
    REASON text NOT NULL,
    STATUS varchar(20) DEFAULT 'Pending',
    APPLIED_DATE timestamp DEFAULT CURRENT_TIMESTAMP,
    APPROVED_BY int(11),
    APPROVED_DATE timestamp NULL,
    COMMENTS text,
    CREATED_AT timestamp DEFAULT CURRENT_TIMESTAMP,
    UPDATED_AT timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (LEAVEID),
    FOREIGN KEY (EMPID) REFERENCES tblemployee(EMPID),
    FOREIGN KEY (LEAVETYPEID) REFERENCES tblleavetype(LEAVETYPEID),
    FOREIGN KEY (APPROVED_BY) REFERENCES tblemployee(EMPID)
);

-- Insert sample data
-- Companies
INSERT INTO tblcompany (COMPANYNAME, COMPANYADDRESS, COMPANYCONTACTNO) VALUES
('Tech Solutions Inc.', '123 Business Ave, Tech City, TC 12345', '+1-555-0101'),
('Digital Innovations', '456 Innovation Blvd, Digital District, DD 67890', '+1-555-0202'),
('Global Systems Ltd.', '789 Enterprise St, Global Plaza, GP 11111', '+1-555-0303');

-- Departments
INSERT INTO tbldepartment (DEPARTMENT, COMPANYID) VALUES
('Human Resources', 1),
('Information Technology', 1),
('Finance', 1),
('Marketing', 1),
('Development', 2),
('Quality Assurance', 2),
('Operations', 3),
('Sales', 3);

-- Leave Types
INSERT INTO tblleavetype (LEAVETYPE, LEAVEDAYS, DESCRIPTION) VALUES
('Annual Leave', 21, 'Annual vacation leave'),
('Sick Leave', 10, 'Medical sick leave'),
('Maternity Leave', 90, 'Maternity leave for mothers'),
('Paternity Leave', 14, 'Paternity leave for fathers'),
('Emergency Leave', 5, 'Emergency personal leave'),
('Study Leave', 7, 'Educational study leave');

-- Employees
INSERT INTO tblemployee (EMPLOYID, FNAME, LNAME, MNAME, ADDRESS, EMAIL, PHONE, COMPANYID, DEPARTMENTID, POSITION, DATEHIRED, USERNAME, PASS, TYPE) VALUES
('EMP001', 'John', 'Doe', 'Smith', '123 Main St, Anytown, AT 12345', 'john.doe@email.com', '+1-555-1001', 1, 1, 'HR Manager', '2022-01-15', 'jdoe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin'),
('EMP002', 'Jane', 'Smith', 'Alice', '456 Oak Ave, Somewhere, SW 67890', 'jane.smith@email.com', '+1-555-1002', 1, 2, 'Senior Developer', '2022-02-20', 'jsmith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Employee'),
('EMP003', 'Mike', 'Johnson', 'Robert', '789 Pine St, Elsewhere, EW 11111', 'mike.johnson@email.com', '+1-555-1003', 1, 3, 'Finance Analyst', '2022-03-10', 'mjohnson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Employee'),
('EMP004', 'Sarah', 'Wilson', 'Marie', '321 Elm Dr, Newplace, NP 22222', 'sarah.wilson@email.com', '+1-555-1004', 2, 5, 'Full Stack Developer', '2022-04-05', 'swilson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Employee'),
('EMP005', 'David', 'Brown', 'William', '654 Maple Ln, Oldtown, OT 33333', 'david.brown@email.com', '+1-555-1005', 3, 7, 'Operations Manager', '2022-05-12', 'dbrown', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Manager');

-- Sample leave requests
INSERT INTO tblleave (EMPID, LEAVETYPEID, STARTDATE, ENDDATE, DAYS, REASON, STATUS, APPROVED_BY, APPROVED_DATE) VALUES
(2, 1, '2024-01-15', '2024-01-19', 5, 'Family vacation', 'Approved', 1, '2024-01-10 10:30:00'),
(3, 2, '2024-01-22', '2024-01-24', 3, 'Medical checkup', 'Approved', 1, '2024-01-20 14:15:00'),
(4, 1, '2024-02-05', '2024-02-09', 5, 'Personal travel', 'Pending', NULL, NULL),
(5, 5, '2024-01-25', '2024-01-25', 1, 'Family emergency', 'Approved', 1, '2024-01-25 09:00:00');

-- Create indexes for better performance
CREATE INDEX idx_employee_company ON tblemployee(COMPANYID);
CREATE INDEX idx_employee_department ON tblemployee(DEPARTMENTID);
CREATE INDEX idx_leave_employee ON tblleave(EMPID);
CREATE INDEX idx_leave_type ON tblleave(LEAVETYPEID);
CREATE INDEX idx_leave_status ON tblleave(STATUS);
CREATE INDEX idx_leave_dates ON tblleave(STARTDATE, ENDDATE);
