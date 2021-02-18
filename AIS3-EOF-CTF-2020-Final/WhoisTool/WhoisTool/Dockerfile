FROM golang:1.15.6-buster

WORKDIR /go/src/app
COPY app.go .

RUN apt update
RUN apt -y install whois

RUN go get -d -v ./...
RUN go build -o app -ldflags "-s -w" app.go

RUN chown root:root --recursive /go
RUN chmod --recursive o-w /go
RUN touch query.log
RUN chmod 0602 query.log

RUN echo "AIS3{fake_flag}" > /flag
RUN chmod 0400 /flag
COPY readflag.c /readflag.c
RUN gcc /readflag.c -o /readflag
RUN chmod 4755 /readflag

CMD [ "./app" ]
