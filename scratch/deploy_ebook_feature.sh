#!/bin/bash
set -e

REMOTE_USER="amisdavc"
REMOTE_HOST="50.87.224.105"
REMOTE_PORT="2222"
REMOTE_PATH="/home2/amisdavc/teacher.amis.edu.ph"
ARCHIVE_NAME="teacher_ebook_deploy.tar.gz"

echo "Bundling eBook feature files for Teacher Portal..."
tar -czf $ARCHIVE_NAME \
    routes/web.php \
    app/Http/Controllers/TeacherPortalController.php \
    app/Models/Ebook.php \
    resources/views/teacher/ebook.blade.php \
    database/migrations/2026_06_08_072225_create_ebooks_and_sso_tables_for_testing.php \
    tests/Feature/EbookTest.php

echo "Uploading bundle to production server..."
scp -o StrictHostKeyChecking=no -P $REMOTE_PORT $ARCHIVE_NAME $REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH/

echo "Extracting bundle on production and clearing caches..."
ssh -o StrictHostKeyChecking=no -p $REMOTE_PORT $REMOTE_USER@$REMOTE_HOST "cd $REMOTE_PATH && tar -xzf $ARCHIVE_NAME && rm $ARCHIVE_NAME && php artisan migrate --force && php artisan config:clear && php artisan cache:clear && php artisan view:clear"

# Clean up local archive
rm -f $ARCHIVE_NAME

echo "Successfully deployed eBook Feature to production!"
