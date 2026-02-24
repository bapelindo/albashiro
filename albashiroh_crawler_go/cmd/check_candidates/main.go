package main

import (
	"crypto/tls"
	"fmt"
	"net/http"
	"os"
	"sync"
	"time"
)

func main() {
	candidates := []string{
		// === INDONESIAN PSYCHOLOGY DEPARTMENTS & SINTA JOURNALS ===
		"https://www.psikologi.ui.ac.id/berita",
		"https://journal.walisongo.ac.id/index.php/Psikohumaniora/index",
		"http://journal.uinsgd.ac.id/index.php/psy",
		"http://jurnal.radenfatah.ac.id/index.php/psikis/issue/archive",
		"http://jurnalfpk.uinsa.ac.id/index.php/JPP",
		"http://journal.uinjkt.ac.id/index.php/jp3i",
		"https://psikologi.ui.ac.id",
		"https://psikologi.ugm.ac.id",
		"https://psikologi.unair.ac.id",
		"https://psikologi.undip.ac.id",
		"https://fpsi.um.ac.id",
		"https://psikologi.ub.ac.id",
		"https://psikologi.upi.edu",
		"https://psikologi.unpad.ac.id",
		"https://fpsi.unm.ac.id",
		"https://fpsi.usu.ac.id",
		"https://psikologi.uns.ac.id",
		"https://psikologi.unej.ac.id",
		"https://psikologi.unnes.ac.id",
		"https://psikologi.uinjkt.ac.id",
		"https://psikologi.uin-suka.ac.id",
		"https://psikologi.uin-malang.ac.id",

		// === GLOBAL MENTAL HEALTH BLOGS (2024/2025 LIST) ===
		"https://www.psychologytoday.com/us/blog/both-sides-couch",
		"https://www.goodtherapy.org/blog",
		"https://www.mentalhealth.org.uk/explore-mental-health/blogs",
		"https://www.mentalhealthamerica.net/blog",
		"https://www.talkspace.com/blog",
		"https://adaa.org/blog",
		"https://www.lawyerswithdepression.com",
		"https://therapyforblackgirls.com/blog",
		"https://www.mindful.org",
		"https://www.blurtitout.org/blog",
		"https://psychcentral.com",
		"https://www.verywellmind.com",
		"https://tinybuddha.com",
		"https://www.nami.org/Blogs",
		"https://www.healthyplace.com/blogs",
		"https://www.bodymindmagazine.com/blog",
		"https://www.psychologytoday.com/us/blog",

		// === ISLAMIC PSYCHOLOGY ORGANIZATIONS ===
		"https://aimsonline.org",
		"https://arabpsynet.com",
		"https://iamphome.org",
		"https://islamicpsychology.org",
		"https://isip.foundation",
		"https://maqasid.org",
		"https://khalilcenter.com",
		"https://sakeenainstitute.com",
		"https://albalaghacademy.org",

		// === INDONESIAN NEWS & MEDIA (Mental Health Sections) ===
		"https://www.cnnindonesia.com/tag/kesehatan-mental",
		"https://www.sindonews.com/tag/kesehatan-mental",
		"https://health.detik.com/mental-health",
		"https://www.tempo.co/tag/kesehatan-mental",
		"https://mediaindonesia.com/tag/kesehatan-mental",
		"https://www.antaranews.com/tag/kesehatan-mental",
		"https://www.rri.co.id/tag/kesehatan-mental",
		"https://www.kemkes.go.id",
		"https://unair.ac.id/news",
		"https://bicarakan.id/blog",
		"https://hatiplong.com/blog",
		"https://pijarpsikologi.org/blog",
		"https://ibunda.id/blog",
		"https://ipkindonesia.or.id/berita",

		// === ADDITIONAL INTERNATIONAL SOURCES ===
		"https://www.apa.org/news/press/releases",
		"https://www.who.int/news-room/fact-sheets/detail/mental-health-strengthening-our-response",
		"https://www.nimh.nih.gov/news/science-news",
		"https://mhanational.org/chiming-in",
		"https://www.bphope.com/blog",
		"https://www.psychiatry.org/news-room/apa-blogs",
		"https://www.counseling.org/news/aca-blogs",
		"https://www.shrinkrap.com",
		"https://thereseborchard.com/blog",
		"https://www.ted.com/topics/mental+health",
		"https://www.calmsage.com/blog",
		"https://www.additudemag.com/blog",
		"https://www.choosingtherapy.com/blog",
		"https://unitedgmh.org/news",
		"https://www.mqmentalhealth.org/news-blog",
		"https://www.mentalhealth.org.uk/news",
		"https://www.rethink.org/news-and-stories",
		"https://www.youngminds.org.uk/about-us/media-centre/press-releases",
		"https://www.mind.org.uk/news-campaigns/news",
		"https://www.samaritans.org/about-samaritans/research-policy/mental-health-statistics-uk",
		"https://www.centreformentalhealth.org.uk/blog",
		"https://www.centreformentalhealth.org.uk/news",
		"https://www.headstogether.org.uk/news",
		"https://www.time-to-change.org.uk/blog",
		"https://www.mentalhealthatwork.org.uk/news",
		"https://www.mhfaengland.org/mhfa-centre/news",
		"https://www.blackdoginstitute.org.au/news",
		"https://www.beyondblue.org.au/media/news",
		"https://www.sane.org/news-and-media",
		"https://www.ruok.org.au/news",
		"https://www.reachout.com/about/news-and-media",
		"https://www.lifeline.org.au/about/news-and-media",
		"https://www.headspace.org.au/about-us/news-and-media",
		"https://www.orygen.org.au/About/News-and-Events",
		"https://www.mhalens.com",
		"https://www.imhcn.org/news",
		"https://www.wfmh.global/news-events",
		"https://www.gmhponline.org/news",
		"https://www.globalmentalhealth.org/news",
		"https://www.mhinnovation.net/news",
		"https://www.autistica.org.uk/news",
		"https://www.combatstress.org.uk/news",
		"https://www.helpforheroes.org.uk/news",
		"https://www.ptsd.va.gov/about/news/index.asp",
		"https://www.tara.org/news",
		"https://www.bbrfoundation.org/blog",
		"https://www.dbsalliance.org/education/newsletters",
		"https://www.iocdf.org/blog",
		"https://www.schizophrenia.com/news",
		"https://www.eatingdisorderhope.com/blog",
		"https://www.nationaleatingdisorders.org/blog",

		// === ADDITIONAL UNIVERSITIES (INDONESIA) ===
		"https://psikologi.uhamka.ac.id",
		"https://psikologi.uad.ac.id",
		"https://psikologi.uai.ac.id",
		"https://psikologi.kemenkes.go.id", // Often has health polytechnic news
		"https://poltekkes-malang.ac.id/berita-kategori/jurusan-promosi-kesehatan",
		"https://fpsi.mercubuana.ac.id",
		"https://psikologi.esaunggul.ac.id",
		"https://psikologi.binus.ac.id",
		"https://psikologi.untar.ac.id",
		"https://psikologi.atmajaya.ac.id",
		"https://psikologi.usm.ac.id",
		"https://psikologi.unissula.ac.id",
		"https://psikologi.udinus.ac.id",
		"https://psikologi.unwahas.ac.id",
		"https://psikologi.upgris.ac.id",
		"https://psikologi.unika.ac.id",
		"https://psikologi.uksw.ac.id",
		"https://psikologi.ums.ac.id",
		"https://psikologi.uny.ac.id",
		"https://psikologi.sanatadharma.ac.id",
	}

	fmt.Printf("🔍 Checking %d NEW candidates for accessibility (Timeout 10s)...\n\n", len(candidates))

	var wg sync.WaitGroup
	semaphore := make(chan struct{}, 20) // 20 concurrent checks

	// Open file to write valid seeds
	f, err := os.Create("valid_seeds.txt")
	if err != nil {
		fmt.Println("Error creating file:", err)
		return
	}
	defer f.Close()

	var validSeeds []string
	var mu sync.Mutex

	for _, seed := range candidates {
		wg.Add(1)
		go func(urlStr string) {
			defer wg.Done()
			semaphore <- struct{}{}
			defer func() { <-semaphore }()

			status, err := checkURL(urlStr)

			if err == nil && status == 200 {
				fmt.Printf("✅ [200] %s\n", urlStr)
				mu.Lock()
				validSeeds = append(validSeeds, urlStr)
				f.WriteString(urlStr + "\n")
				mu.Unlock()
			} else {
				msg := ""
				if err != nil {
					msg = err.Error()
				} else {
					msg = fmt.Sprintf("HTTP %d", status)
				}
				fmt.Printf("❌ [%s] %s\n", msg, urlStr)
			}
		}(seed)
	}

	wg.Wait()
	fmt.Printf("\nSaved %d valid seeds to valid_seeds.txt\n", len(validSeeds))
}

func checkURL(targetURL string) (int, error) {
	client := &http.Client{
		Timeout: 10 * time.Second,
		Transport: &http.Transport{
			TLSClientConfig: &tls.Config{InsecureSkipVerify: true},
		},
	}

	req, err := http.NewRequest("GET", targetURL, nil)
	if err != nil {
		return 0, err
	}

	// Use a realistic browser User-Agent
	req.Header.Set("User-Agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36")
	req.Header.Set("Accept", "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8")

	resp, err := client.Do(req)
	if err != nil {
		return 0, err
	}
	defer resp.Body.Close()

	return resp.StatusCode, nil
}
