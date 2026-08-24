const fs = require('fs');
let content = fs.readFileSync('c:/xampp/htdocs/sana_project/Final_Project/NTTI_Teacher_Attendent/ntti-attendance/database_backup_clean.sql', 'utf8');

content = content.replace(/name_kh, gender, email/g, 'name_kh, email');
// the value is `..., NULL, 'email@...`
// we need to be careful with replace
// Let's replace the whole string 'name_kh, gender, email, phone, department, position, status, photo'
// and for values, since gender is always NULL in the script:
content = content.replace(/, NULL, '/g, ", '"); 
// wait, what if name_kh is NULL? It would be `, NULL, NULL, 'email'`
// A safer way is to use regex:
content = content.replace(/name_kh, gender, email/g, 'name_kh, email');

let lines = content.split('\n');
for (let i = 0; i < lines.length; i++) {
    if (lines[i].includes('INSERT INTO teachers')) {
        // the 5th value is gender.
        // VALUES (16, 'T0016', 'VANN PHAY', '...', NULL, 'vann.phay@yahoo.com'
        lines[i] = lines[i].replace(/, NULL, '(.*?)', '(.*?)', '(.*?)', '(.*?)', '(.*?)', NULL, '/, ", '$1', '$2', '$3', '$4', '$5', NULL, '");
    }
}
fs.writeFileSync('c:/xampp/htdocs/sana_project/Final_Project/NTTI_Teacher_Attendent/ntti-attendance/database_backup_clean_fixed.sql', content);
