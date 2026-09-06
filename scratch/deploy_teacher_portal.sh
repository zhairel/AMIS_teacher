#!/bin/bash
set -e

REMOTE_USER="amisdavc"
REMOTE_HOST="50.87.224.105"
REMOTE_PORT="2222"
REMOTE_PATH="/home2/amisdavc/faculty.amis.edu.ph"
ARCHIVE_NAME="teacher_portal_deploy.tar.gz"

echo "Bundling updated teacher portal files..."
tar -czf $ARCHIVE_NAME \
    routes/web.php \
    app/Http/Controllers/TeacherPortalController.php \
    resources/views/teacher/meetings.blade.php \
    resources/views/teacher/subject-workspace.blade.php \
    resources/views/teacher/layout.blade.php \
    resources/views/teacher/digital-id.blade.php \
    resources/views/teacher/announcements.blade.php \
    resources/views/teacher/attendance/index.blade.php \
    app/Http/Controllers/AttendanceController.php \
    app/Services/TeacherPortalService.php \
    app/Services/ZKTecoParser.php \
    app/Models/SubjectAnnouncement.php \
    app/Models/StudentAttendance.php \
    app/Models/User.php \
    database/migrations/2026_07_06_170558_add_audience_to_subject_announcements_table.php \
    database/migrations/2026_07_06_171857_create_student_attendances_table.php \
    database/migrations/2026_07_06_172533_add_biometric_id_to_users_table.php \
    resources/images/payment_reminder.png \
    app/Mail/PaymentReminderMail.php \
    resources/views/emails/payment_reminder.blade.php \
    app/Console/Commands/SendPaymentReminders.php \
    public/build

echo "Uploading bundle to teacher portal production server..."
scp -o StrictHostKeyChecking=no -P $REMOTE_PORT $ARCHIVE_NAME $REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH/

echo "Extracting bundle on production, running migrations, and clearing caches..."
ssh -o StrictHostKeyChecking=no -p $REMOTE_PORT $REMOTE_USER@$REMOTE_HOST "cd $REMOTE_PATH && tar -xzf $ARCHIVE_NAME && rm $ARCHIVE_NAME && php artisan migrate --force && php artisan config:clear && php artisan cache:clear && php artisan view:clear"

# Clean up local archive
rm -f $ARCHIVE_NAME

echo "Successfully deployed Teacher Portal updates to production!"
