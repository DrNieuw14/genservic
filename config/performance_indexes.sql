-- Run once to improve attendance/report loading speed.
CREATE INDEX idx_attendance_date_user ON attendance (date, user_id);
CREATE INDEX idx_attendance_status_date ON attendance (status, date);
CREATE INDEX idx_personnel_fullname ON personnel (fullname);