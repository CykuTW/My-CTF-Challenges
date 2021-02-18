FROM node:14

# Create app directory
RUN mkdir -p /usr/src/app

WORKDIR /usr/src/app

COPY src/ .

RUN npm install

RUN echo "AIS3{fake_flag}" > /flag
RUN chmod 0400 /flag
COPY readflag.c /readflag.c
RUN gcc /readflag.c -o /readflag
RUN chmod 4755 /readflag

EXPOSE 5000

CMD [ "node", "app.js" ]
