FROM nginx:1.13.7

COPY ./default.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

ENV NGINX_HOST localhost
ENV NGINX_PORT 80

RUN  envsubst '$$NGINX_HOST $$NGINX_PORT' < /etc/nginx/conf.d/default.conf > /etc/nginx/conf.d/default.conf

