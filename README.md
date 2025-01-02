# sql_backup_program


Backup Instructions


Step 1: Access the Backup Page

Open your web browser and navigate to the backup page. The URL should be http://192.168.1.210:1111.



Step 2: Fill in the Database Information

    Database Host: Enter the hostname of your database server. This is usually localhost if your database is on the same server as your web application.
    Username: Enter the username for your database.
    Password: Enter the password for your database.
    Database Name: Enter the name of the database you want to back up.
    Backup Directory: Enter the directory on your local PC where you want to save the backup file. For example, C:\backups.

Step 3: Start the Backup Process

Click the "Start Backup" button to begin the backup process.



Step 4: Download the Backup File

Once the backup process is complete, you will see a success message and a "Download Backup" button.

Click the "Download Backup" button to download the backup file to your local PC.
Step 5: Save the Backup File

Your browser will prompt you to save the backup file. Choose the location on your local PC where you want to save the file and click "Save".
Step 6: Verify the Backup File

Navigate to the directory where you saved the backup file.

Ensure that the backup file is present and has the correct name and extension (e.g., backup-yourdatabase-YYYY-MM-DD-HH-MM-SS.sql.gz).
Additional Tips

    Backup Regularly: It's a good practice to back up your database regularly to prevent data loss.
    Store Backups Safely: Keep your backup files in a secure location, and consider using external storage or cloud services for added safety.
    Test Restores: Periodically test restoring your backups to ensure they are valid and can be used to recover your data if needed.

