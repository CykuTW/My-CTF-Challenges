package main

import (
	"fmt"
	"log"
	"net"
	"net/http"
	"os"
	"os/exec"
	"strings"

	"github.com/kataras/go-sessions"
)

const (
	TEMPLATE_BASE_HEAD = `<!DOCTYPE html><html lang="en"><head><title>Whois Tool</title></head><body>`
	TEMPLATE_BASE_FOOT = `</body></html>`
	TEMPLATE_INDEX = TEMPLATE_BASE_HEAD + `<h3>Whois Tool</h3><form method="POST" action="/set"><label for="domain">Domain: </label><input type="text" name="domain"><br /><br /><button type="submit">submit</button></form>` + TEMPLATE_BASE_FOOT
	TEMPLATE_RESULT = TEMPLATE_BASE_HEAD + `<textarea cols="100" rows="30">%s</textarea><br /><br /><a href="/">Back</a>` + TEMPLATE_BASE_FOOT
	TEMPLATE_ERROR = TEMPLATE_BASE_HEAD + `<h3>Error</h3><p>%s</p><br /><br /><a href="/">Back</a>` + TEMPLATE_BASE_FOOT
)

func isValidDomain(domain string) bool {
	return !strings.Contains(domain, "'") && !strings.Contains(domain, "\\") && !strings.HasPrefix(domain, "-")
}

func main() {
	sess := sessions.New(sessions.Config{})

	f, err := os.OpenFile("query.log", os.O_APPEND|os.O_CREATE|os.O_WRONLY, 0666)
	if err != nil {
		panic("Can't open ./query.log")
	}
	log.SetOutput(f)
	defer f.Close()

	http.HandleFunc("/set", func (w http.ResponseWriter, req *http.Request) {
		s := sess.Start(w, req)

		if req.Method != http.MethodPost {
			return
		}

		s.Set("domain", req.PostFormValue("domain"))
		http.Redirect(w, req, "/result", http.StatusSeeOther)
	})

	http.HandleFunc("/result", func (w http.ResponseWriter, req *http.Request) {
		s := sess.Start(w, req)

		var html string

		if (!isValidDomain(s.GetString("domain"))) {
			html = fmt.Sprintf(TEMPLATE_ERROR, "Invalid input")
			w.Write([]byte(html))
			return
		}

		ip, _, _ := net.SplitHostPort(req.RemoteAddr)
		log.Printf("- %s - %s\n", ip, s.GetString("domain"))

		cmd := exec.Command("/bin/sh", "-c", fmt.Sprintf("whois '%s'", s.GetString("domain")))
		out, err := cmd.CombinedOutput()
		if err != nil {
			html = fmt.Sprintf(TEMPLATE_ERROR, fmt.Sprintf("Error() err: %v", err))
		} else {
			w.Header().Set("Content-Type", "text/html; charset=UTF-8")
			html = fmt.Sprintf(TEMPLATE_RESULT, out)
		}

		w.Header().Set("Content-Type", "text/html; charset=UTF-8")
		w.Write([]byte(html))
	})

	http.HandleFunc("/", func (w http.ResponseWriter, req *http.Request) {
		s := sess.Start(w, req)
		s.Clear()
		w.Header().Set("Content-Type", "text/html; charset=UTF-8")
		w.Write([]byte(TEMPLATE_INDEX))
	})

	http.ListenAndServe(":8000", nil)
}
