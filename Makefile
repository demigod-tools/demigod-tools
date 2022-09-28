APP              := demigod-tool
REPO_NAME        ?= demigod-tools/$(APP)
VERSION          ?= $(shell cat ./.version)


deps:  ## TODO
	## TODO
	##  ${APP}
	##  ${REPO_NAME}
	##  ${VERSION}


version-bump:  ##  Increase the version number by one
	bumpversion minor
	git push origin master --tags

release:
	gh release create "${VERSION}" --generate-notes
