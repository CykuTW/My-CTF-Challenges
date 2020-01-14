FROM node:12

# Create app directory
RUN mkdir -p /usr/src/app
RUN useradd appuser

WORKDIR /usr/src/app

COPY . .

RUN npm install

EXPOSE 49007

USER appuser:appuser
CMD [ "node", "app.js" ]
