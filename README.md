# Rvvup AX Invoice Plugin

## Dockerized Setup of Test Store

If you would like to have a quick local installation of the plugin on a magento store (for testing), you can follow these steps:

- Copy .env.sample to .env and update the values as needed.
- Run the following command to start the docker containers:
```
docker-compose up -d --build
```

- The magento store, once it has completed start up, will be available at https://local.dev.rvvuptech.com/
- Add your companyId and rvvup api key mapping in the admin dashboard