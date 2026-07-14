#!/bin/bash
set -e

REMOTE_USER="amisdavc"
REMOTE_HOST="50.87.224.105"
REMOTE_PORT="2222"
REMOTE_PATH="/home2/amisdavc/teacher.amis.edu.ph"
ARCHIVE_NAME="deploy_dynamic_roster.tar.gz"

echo "Bundling updated teacher portal service..."
tar -czf $ARCHIVE_NAME \
    app/Services/TeacherPortalService.php

echo "Uploading bundle to production server..."
scp -o StrictHostKeyChecking=no -P $REMOTE_PORT $ARCHIVE_NAME $REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH/

echo "Extracting bundle on production and clearing view/cache..."
ssh -o StrictHostKeyChecking=no -p $REMOTE_PORT $REMOTE_USER@$REMOTE_HOST "cd $REMOTE_PATH && tar -xzf $ARCHIVE_NAME && rm $ARCHIVE_NAME && php artisan config:clear && php artisan cache:clear && php artisan view:clear"

# Clean up local archive
rm -f $ARCHIVE_NAME

echo "Dynamic student roster fallback deployed successfully!"
