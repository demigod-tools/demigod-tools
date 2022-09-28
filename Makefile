APP                        := pantheonapi
REPO_NAME                  ?= pantheon-systems/$(APP)






version-bump:  ##  Increase the version number by one
	bumpversion minor
	git push origin master --tags