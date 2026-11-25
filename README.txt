MUNICIPAL SERVICE FINDER  (PHP + MySQL, XAMPP)

SETUP
1. Copy this folder to  C:\xampp\htdocs\municipal-service-finder
2. Start Apache and MySQL in the XAMPP Control Panel.
3. Open  http://localhost/municipal-service-finder/install.php
   Set your admin username/password, click Install.
4. Open  http://localhost/municipal-service-finder/
5. DELETE install.php after installing (running it again erases all data).

If your MySQL has a root password, edit DB_PASS in config.php.
Change LGU_NAME in config.php to the real municipality.

PUBLIC PAGES   index, services (search + categories), service (details), offices, office, directions
ADMIN          /admin/login.php  -> dashboard, services, offices, categories, change password

All sample data is illustrative. Verify it with the real offices, then use
"Verified today" in Admin > Services so the "may be out of date" warning goes away.
