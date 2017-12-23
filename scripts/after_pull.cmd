cd ..
php bin/console doctrine:migrations:migrate -q
php bin/console doctrine:fixtures:load -q
yarn encore dev