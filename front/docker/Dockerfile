# syntax=docker/dockerfile:1.7

FROM node:22-alpine

WORKDIR /app

COPY package.json package-lock.json ./

RUN --mount=type=cache,target=/root/.npm \
    npm config set fetch-retries 5 \
    && npm config set fetch-retry-mintimeout 20000 \
    && npm config set fetch-retry-maxtimeout 120000 \
    && npm ci --no-audit --no-fund --prefer-offline

COPY --chmod=755 docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint

EXPOSE 5173

ENTRYPOINT ["docker-entrypoint"]
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0", "--port", "5173", "--strictPort"]
