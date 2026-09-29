# syntax=docker/dockerfile:1.7

FROM node:22-alpine AS deps

WORKDIR /app

COPY package.json package-lock.json ./

RUN --mount=type=cache,target=/root/.npm \
    npm config set fetch-retries 5 \
    && npm config set fetch-retry-mintimeout 20000 \
    && npm config set fetch-retry-maxtimeout 120000 \
    && npm ci --no-audit --no-fund --prefer-offline

FROM deps AS development

COPY --chmod=755 docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint

EXPOSE 5173

ENTRYPOINT ["docker-entrypoint"]
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0", "--port", "5173", "--strictPort"]

FROM deps AS build

COPY . .

ARG VITE_API_URL=/api
ENV VITE_API_URL=$VITE_API_URL

RUN npm run build-only

FROM alpine:3.20 AS certs

RUN apk add --no-cache openssl \
    && mkdir -m 755 /certs \
    && openssl req -x509 -nodes -days 3650 -newkey rsa:2048 \
        -keyout /certs/server.key \
        -out /certs/server.crt \
        -subj "/CN=localhost" \
    && chmod 400 /certs/server.key \
    && chmod 444 /certs/server.crt

FROM nginxinc/nginx-unprivileged:1.27-alpine AS production

COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/security-headers.conf /etc/nginx/snippets/security-headers.conf
COPY docker/uploads-headers.conf /etc/nginx/snippets/uploads-headers.conf
COPY --from=certs --chown=nginx:nginx /certs /etc/nginx/certs
COPY --from=build /app/dist /usr/share/nginx/html

EXPOSE 8080 8443

HEALTHCHECK --interval=30s --timeout=3s --start-period=10s --retries=5 \
    CMD wget --no-check-certificate -qO- https://127.0.0.1:8443/ >/dev/null || exit 1
