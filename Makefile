all:
	echo ""

behat:
	bin/behat.bat @XczimiPredictBundle --format=pretty,html	--out=,web/bdd.html --ansi

behatinprogress:
	bin/behat.bat @XczimiPredictBundle --format=pretty,html	--out=,web/bdd.html --ansi --tags inprogress
	
mongodbcode:
	for i in documents repositories hydrators; do \
		app/console doctrine:mongodb:generate:$$i XczimiPredictBundle; \
	done

github:
	git push
	
installprod:
	php app/console cache:clear -e prod
	php app/console assets:install web