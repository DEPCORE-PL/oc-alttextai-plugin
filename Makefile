phpdoc_install:
	composer require --dev saggre/phpdocumentor-markdown phpdocumentor/shim

phpdoc_make:
	vendor/bin/phpdoc --directory=. --target=docs/api --ignore=vendor --template="vendor/saggre/phpdocumentor-markdown/themes/markdown"
