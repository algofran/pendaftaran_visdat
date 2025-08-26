-- Update script to add KTP and Ijazah file columns to existing database
-- Run this script if you already have an existing applications table

USE visdat_recruitment;

-- Add KTP and Ijazah file columns to applications table
ALTER TABLE applications 
ADD COLUMN ktp_file VARCHAR(255) AFTER photo_file,
ADD COLUMN ijazah_file VARCHAR(255) AFTER ktp_file;

-- Verify the new columns were added
DESCRIBE applications;