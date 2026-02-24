package config

import (
	"bufio"
	"fmt"
	"os"
	"strings"
)

// loadSeeds loads seed URLs from file or returns default list
func loadSeeds() []string {
	// Try to load from file first
	seedFile := "seeds/mental_health_islamic_psychology_2025.txt"
	if seeds, err := loadSeedsFromFile(seedFile); err == nil && len(seeds) > 0 {
		fmt.Printf("   📂 Loaded %d seeds from %s\n", len(seeds), seedFile)
		return seeds
	}

	// Fallback to hardcoded seeds (PRIORITAS ARTIKEL PRAKTIS)
	fmt.Println("   📂 Using default seed list (300+ sources)")
	return getDefaultSeeds()
}

func loadSeedsFromFile(filename string) ([]string, error) {
	file, err := os.Open(filename)
	if err != nil {
		return nil, err
	}
	defer file.Close()

	var seeds []string
	scanner := bufio.NewScanner(file)
	for scanner.Scan() {
		line := strings.TrimSpace(scanner.Text())
		// Skip comments and empty lines
		if line == "" || strings.HasPrefix(line, "#") {
			continue
		}
		// Valid URL
		if strings.HasPrefix(line, "http") {
			seeds = append(seeds, line)
		}
	}

	if err := scanner.Err(); err != nil {
		return nil, err
	}

	return seeds, nil
}

func getDefaultSeeds() []string {
	return []string{
		// =========================================================================
		// CATEGORY 1: BLOG KESEHATAN MENTAL (60 URLs) - PRIORITAS
		// =========================================================================
		"https://satupersen.net/blog",
		"https://ibunda.id/blog",
		"https://psykay.co.id/artikel",
		"https://mindspace.co.id/blog",
		"https://indopsycare.com/blog",
		"https://bicarakan.id/artikel",
		"https://temancurhat.id/blog",
		"https://teduh.id/artikel",
		"https://alpas.id/blog",
		"https://pijarpsikologi.org/blog",
		"https://riliv.co/blog",
		"https://kalm.id/artikel",
		"https://kariib.id/blog",
		"https://psikologimu.com/artikel",
		"https://klee.id/blog",
		"https://halodoc.com/blog/kesehatan-mental",
		"https://alodokter.com/kesehatan-mental",
		"https://klikdokter.com/psikologi",
		"https://sehatq.com/artikel/kesehatan-mental",
		"https://honestdocs.id/kesehatan-mental",
		"https://prodiadigital.com/blog",
		"https://rspp.co.id/artikel",
		"https://klinikutamasehatmulia.com/blog",
		"https://3wellness.com/blog",
		"https://sehatmental.id/artikel",
		"https://talkmentalhealthid.org/blog",
		"https://yayasan-pkm.org/artikel",
		"https://indonesiasehatjiwa.com/blog",
		"https://naluri.life/id/blog",
		"https://tumbuhbersama.co/artikel",

		// =========================================================================
		// CATEGORY 2: ISLAMIC PSYCHOLOGY & COUNSELING (70 URLs)
		// =========================================================================
		"https://konselormuslim.com/artikel",
		"https://relasiconsulting.com/blog",
		"https://syaria.id/artikel",
		"https://masjid-sundakelapa.id/artikel",
		"https://muslim.or.id/kesehatan-jiwa",
		"https://rumahfiqih.com/psikologi-islam",
		"https://konsultasisyariah.com/artikel",
		"https://islampos.com/kesehatan-mental",
		"https://muslimahnews.net/kesehatan-mental",
		"https://nu.or.id/kesehatan-mental",
		"https://muhammadiyah.or.id/kesehatan-mental",
		"https://isip.foundation/blog",
		"https://kundurnews.co.id/islam",
		"https://kompasiana.com/tag/psikologi-islam",
		"https://stmikkomputama.ac.id/blog",
		"https://tazkiyahnufus.com/artikel",
		"https://pesantren.id/tazkiyah",
		"https://dakwatuna.com/psikologi-islam",
		"https://hidayatullah.com/kajian/psikologi-islam",

		// =========================================================================
		// CATEGORY 3: LIFESTYLE & WELLNESS (50 URLs)
		// =========================================================================
		"https://ultimagz.com/kesehatan-mental",
		"https://forumkeadilansumut.com/kesehatan",
		"https://tumakarir.com/blog",
		"https://popmama.com/life/health/mental-health",
		"https://fimela.com/lifestyle/kesehatan-mental",
		"https://wolipop.detik.com/health",
		"https://female.kompas.com/kesehatan",
		"https://idntimes.com/health/mental",
		"https://hipwee.com/kesehatan-mental",
		"https://youthmanual.com/kesehatan-mental",

		// =========================================================================
		// CATEGORY 4: GOVERNMENT & PUBLIC HEALTH (30 URLs)
		// =========================================================================
		"https://sehajiwa.kemkes.go.id",
		"https://healing119.id",
		"https://kemkes.go.id/berita",
		"https://kemenkopmk.go.id/berita",
		"https://dinkes.jakarta.go.id/berita",
		"https://dinkes-jatim.go.id/berita",
		"https://rsj.acehprov.go.id/berita",
		"https://jakarta.go.id/sahabat-jiwa",

		// =========================================================================
		// CATEGORY 5: KONSELING PLATFORMS (40 URLs)
		// =========================================================================
		"https://konselingindonesia.com/artikel",
		"https://bersamabisafoundation.org/blog",
		"https://ipkindonesia.or.id/artikel",
		"https://yayasanpulih.org/artikel",
		"https://berbagicerita.id/blog",
	}
}
