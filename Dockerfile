FROM mariadb:latest

# Set the environment variables
ENV MYSQL_ROOT_PASSWORD=root_password
ENV MYSQL_DATABASE=emergency_care

# Copy the SQL script to initialize the database
COPY emergency_care_schema.sql /docker-entrypoint-initdb.d/
