# Docker support

## Test drive
Bibliograph comes with a very simple docker image that is build each time a Travis CI build runs and is then published
to https://hub.docker.com/r/cboulanger/bibliograph. You can run this image very easily on your local docker-enabled
workstation:

```bash
sudo docker run --rm -p 80:80 cboulanger/bibliograph
```
This will remove the container and its data when you shut down the process.

For a daemonized process, run
```bash
sudo docker run -d -p 80:80 cboulanger/bibliograph
```
You can log with the following credentials (username/password):

- user/user
- manager/manager
- admin/admin

The docker setup is ONLY for testing and I strictly advise against using it in any kind of production setting. If you can contribute a Dockerfile to build a full-fledged appliance of Bibliograph, please share!

## Using the docker image for development

During developemnt, it is very useful to see whether a new installation on an empty OS works, and docker is perfect for that. You can use the following scripts (run `npm run list` to see more):

- `npm run docker-build-all` builds the production version, creates a docker image, and starts a container.
- `npm run docker-log` shows the application and error log of the application running in this container
- `npm run docker-remove-all` to stop and remove the container and delete all images (this is necessary since a change in the sources is not always noticed by the docker script)