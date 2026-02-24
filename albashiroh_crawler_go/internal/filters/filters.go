package filters

import (
	"regexp"
	"strings"
)

// BAD_KEYWORDS - Complete blacklist from Python version (150+ keywords)
var BlacklistKeywords = []string{
	// Politik & Pemerintahan (30 keywords)
	"jokowi", "prabowo", "gibran", "anies", "ganjar", "partai", "dpr", "menteri",
	"pilkada", "pemilu", "debat", "kampanye", "koalisi", "resuffle", "kabinet",
	"kpk", "polisi", "aparat", "bnpb", "basarnas", "bmkg", "kemenag", "mui", "fatwa",
	"gubernur", "bupati", "walikota", "caleg", "dprd", "legislatif",

	// Kriminal & Bencana (25 keywords)
	"tewas", "ditangkap", "korupsi", "kebakaran", "banjir", "longsor", "gempa",
	"tsunami", "korban jiwa", "meninggal dunia", "kecelakaan", "lalu lintas",
	"pembunuhan", "kriminal", "tangkap", "penjara", "hukum", "sidang", "vonis",
	"tabrak", "tabrakan", "terbakar", "tenggelam", "hilang", "ditemukan tewas",

	// Bisnis & Ekonomi (20 keywords)
	"saham", "rupiah", "harga emas", "laba", "dividen", "investasi", "ihsg",
	"ekonomi", "bisnis", "tarif", "cukai", "pajak", "subsidi", "bansos",
	"inflasi", "deflasi", "resesi", "startup", "unicorn", "ipo",

	// Olahraga & Hiburan (20 keywords)
	"timnas", "bola", "liga", "olahraga", "badminton", "motogp", "f1",
	"seleb", "artis", "film", "musik", "konser", "tiket", "selebriti",
	"piala dunia", "olimpiade", "sea games", "asian games", "sepak bola", "basket",

	// Fisik Health - Bukan Mental (40 keywords)
	"kulit", "wajah", "jerawat", "kecantikan", "miss v", "organ intim", "vagina", "penis",
	"hamil", "kehamilan", "menyusui", "imunisasi", "vaksin", "kb", "kontrasepsi",
	"diabetes", "jantung", "kanker", "asam urat", "kolesterol", "hipertensi", "stroke",
	"diet", "berat badan", "langsing", "gemuk", "kurus", "otot", "fitness", "gym",
	"resep", "masak", "kuliner", "makanan", "minuman", "nutrisi", "vitamin",
	"gigi", "mata", "telinga", "hidung",

	// Hewan Peliharaan (15 keywords)
	"anjing", "kucing", "hewan", "peliharaan", "pet", "veteriner", "dokter hewan",
	"kutu", "bulu", "cacingan", "rabies", "hamster", "kelinci", "burung", "ikan",

	// Lain-lain Tidak Relevan (20 keywords)
	"wisata", "travel", "zodiak", "shio", "ramalan", "horoskop", "fengshui",
	"casino", "slot", "poker", "togel", "gacor", "zeus", "pragmatic", "judi",
	"iklan", "advertisement", "promo", "diskon", "jual",

	// Q&A Keywords - User questions (bukan artikel)
	"dok", "dokter", "saya", "aku", "tanya", "bertanya", "mohon", "tolong", "solusi",
	"kenapa", "mengapa", "apakah", "bagaimana", "gimana", "normalkah", "wajarkah",
	"hamilkah", "bisa sembuh", "obatnya apa", "resep obat", "keluhan", "sakit apa",
	"bantu saya", "tolong jawab", "minta saran", "butuh bantuan",

	// Admin Pages - Static pages
	"about us", "tentang kami", "redaksi", "pedoman", "kebijakan", "privasi", "privacy",
	"disclaimer", "contact", "hubungi kami", "board of directors", "karir", "lowongan",
	"iklan", "mediakit", "sitemap", "login", "register", "signin", "signup", "daftar",
	"terms of service", "syarat ketentuan", "faq", "bantuan", "help center",
}

// WHITELIST_KEYWORDS - Complete whitelist from Python version (80+ keywords)
var WhitelistKeywords = []string{
	// Mental Health Disorders (30 keywords)
	"depresi", "depression", "anxiety", "cemas", "kecemasan", "gelisah", "panik",
	"skizofrenia", "schizophrenia", "bipolar", "ocd", "gangguan", "disorder",
	"ptsd", "trauma", "stress", "stres", "burnout", "overwhelmed",
	"bunuh diri", "suicide", "self harm", "luka batin", "menyakiti diri",
	"panic attack", "fobia", "phobia", "social anxiety", "generalized anxiety",

	// Mental Health Professionals & Treatment (25 keywords)
	"psikolog", "psikiater", "terapis", "konselor", "counselor", "therapist",
	"psikoterapi", "psychotherapy", "terapi", "therapy", "treatment",
	"konseling", "counseling", "rehabilitasi", "rehabilitation",
	"cognitive behavioral", "cbt", "mindfulness", "meditasi", "meditation",
	"self care", "perawatan diri", "mental wellness", "kesejahteraan mental",
	"support group", "kelompok dukungan",

	// Mental Health General (15 keywords)
	"jiwa", "mental", "psikologi", "psychology", "kesehatan mental",
	"mental health", "well-being", "kesejahteraan", "emosi", "emotion",
	"perasaan", "feeling", "mood", "suasana hati", "psychological",

	// Islamic Psychology (30 keywords)
	"tafsir", "surah", "ayat", "al-quran", "quran", "hadits", "hadith",
	"ibadah", "akhlak", "fiqih", "syariah", "sunnah", "rasulullah",
	"tazkiyatun nufus", "tazkiyah", "penyucian jiwa", "pembersihan hati",
	"sholat", "shalat", "tahajud", "puasa", "fasting", "zikir", "dzikir",
	"doa", "prayer", "ruqyah", "sabar", "patience", "syukur", "gratitude",
	"tawakkal", "ikhlas", "sincere", "khusyu",

	// Relationships & Family (Mental Health Context) (20 keywords)
	"patah hati", "heartbreak", "putus cinta", "breakup", "jodoh", "soulmate",
	"pernikahan", "marriage", "suami", "husband", "istri", "wife", "pasangan", "partner",
	"keluarga", "family", "orang tua", "parents", "anak", "children", "remaja", "teenager",
	"pelecehan", "abuse", "kekerasan seksual", "sexual violence", "korban", "victim",
	"pemulihan", "recovery", "healing", "baby blues", "postpartum", "postnatal",
}

// URL patterns to reject (RELAXED - only truly problematic URLs)
var URLBlacklist = []string{
	// User-generated content (Q&A, forums)
	"/tanya-dokter/", "/komunitas/", "/forum/", "/diskusi/", "/community/", "/conversation/",
	"/user/", "/profile/", "/comment/", "/reply/",

	// Auth & Admin
	"/auth/", "/login/", "/register/", "/search/",
	"/wp-admin/", "/wp-content/", "/wp-json/",

	// Static/Meta pages
	"/feed/", "/rss/", "/sitemap/", "/tag/", "/category/", "/author/",

	// Administrative pages (STRICT - only exact matches)
	"visi-misi", "tentang-kami", "about-us", "contact-us", "hubungi-kami",
	"lowongan", "karir", "career", "job-vacancy",

	// NOTE: Removed "struktur-", "pimpinan-", "senat-", "dewan-", "kepakaran-", "kelompok-riset"
	// These were blocking legitimate academic/research content!
}

func IsBlacklisted(url string) bool {
	urlLower := strings.ToLower(url)
	for _, pattern := range URLBlacklist {
		if strings.Contains(urlLower, pattern) {
			return true
		}
	}
	return false
}

// Article URL patterns (must match at least one)
var ArticlePatterns = []string{
	"/artikel/", "/article/", "/post/", "/blog/",
	"/news/", "/berita/", "/story/", "/read/",
	// "-" removed, handled by logic
}

// Valid article URL regex patterns
var ArticleUrlRegex = []*regexp.Regexp{
	regexp.MustCompile(`/\d{4}/\d{2}/\d{2}/`), // Year/Month/Day
	regexp.MustCompile(`/\d{4}/\d{2}/`),       // Year/Month
	regexp.MustCompile(`/\d{4}/`),             // Year (weak but useful)
}

func IsValidArticle(url, title, content string) bool {
	urlLower := strings.ToLower(url)
	titleLower := strings.ToLower(title)
	contentLower := strings.ToLower(content)

	// Check URL blacklist
	for _, pattern := range URLBlacklist {
		if strings.Contains(urlLower, pattern) {
			return false
		}
	}

	// Check if URL looks like an article (Stricter Logic)
	hasArticlePattern := false

	// 1. Check strict keywords in path
	for _, pattern := range ArticlePatterns {
		if pattern == "-" {
			continue
		} // Skip simple dash check
		if strings.Contains(urlLower, pattern) {
			hasArticlePattern = true
			break
		}
	}

	// 2. Check regex patterns (Dates)
	if !hasArticlePattern {
		for _, re := range ArticleUrlRegex {
			if re.MatchString(urlLower) {
				hasArticlePattern = true
				break
			}
		}
	}

	// 3. Fallback: If it has at least 3 dashes AND path depth > 1 (to avoid root pages like /visi-misi)
	if !hasArticlePattern {
		dashCount := strings.Count(urlLower, "-")
		slashCount := strings.Count(urlLower, "/") - 2 // approx depth (remove proto://)

		// Heuristic: Articles usually have long slugs (many dashes)
		if dashCount >= 3 && slashCount >= 1 {
			hasArticlePattern = true
		}
	}

	if !hasArticlePattern {
		return false
	}

	// Check blacklist keywords (reject if found)
	combinedText := titleLower + " " + contentLower
	for _, keyword := range BlacklistKeywords {
		if strings.Contains(combinedText, keyword) {
			return false // Reject if blacklist keyword found
		}
	}

	// RELAXED FILTER: Accept if no blacklist keywords found
	// Whitelist is now OPTIONAL (bonus points, not required)
	// This allows broader psychology content through

	// Accept article if:
	// 1. No blacklist keywords found (already checked above)
	// 2. Has article-like URL pattern (already checked above)
	// Whitelist is now optional, not mandatory
	return true // Accept by default if passed blacklist check
}

func CleanContent(content string) string {
	// 1. Remove specific spam/marketing phrases (Case Insensitive)
	// We do this BEFORE collapsing whitespace so we can handle line-based spam safely.

	spamPatterns := []*regexp.Regexp{
		// Discount & Promo
		regexp.MustCompile(`(?i)diskon\s+s/d\s+\d+%`),
		regexp.MustCompile(`(?i)spesial\s+hari\s+ini`),
		regexp.MustCompile(`(?i)psikotes\s+online\s+premium`),
		regexp.MustCompile(`(?i)promo\s+terbatas`),
		regexp.MustCompile(`(?i)flash\s+sale`),
		regexp.MustCompile(`(?i)kode\s+voucher`),
		regexp.MustCompile(`(?i)belanja\s+sekarang`),

		// Social Media & CTA (Restricted to line or sentence end)
		regexp.MustCompile(`(?i)follow\s+kami\s+di[^\n]*`),
		regexp.MustCompile(`(?i)kunjungi\s+instagram[^\n]*`),
		regexp.MustCompile(`(?i)cek\s+youtube[^\n]*`),
		regexp.MustCompile(`(?i)jangan\s+lupa\s+like[^\n]*`),
		regexp.MustCompile(`(?i)klik\s+di\s+sini`),
		regexp.MustCompile(`(?i)baca\s+selengkapnya`),
		regexp.MustCompile(`(?i)simak\s+video[^\n]*`),
		regexp.MustCompile(`(?i)bergabung\s+dengan\s+komunitas[^\n]*`),
		regexp.MustCompile(`(?i)download\s+aplikasi[^\n]*`),
		regexp.MustCompile(`(?i)unduh\s+sekarang[^\n]*`),

		// Editorial & Credits (Start of line only)
		regexp.MustCompile(`(?m)^penulis:.*$`),
		regexp.MustCompile(`(?m)^editor:.*$`),
		regexp.MustCompile(`(?m)^sumber:.*$`),
		regexp.MustCompile(`(?m)^foto:.*$`),
		regexp.MustCompile(`(?m)^laporan:.*$`),
		regexp.MustCompile(`(?i)copyright\s+©[^\n]*`),
		regexp.MustCompile(`(?i)hak\s+cipta\s+dilindungi[^\n]*`),

		// Generic Navigation/Filler
		regexp.MustCompile(`(?i)baca\s+juga:?[^\n]*`),
		regexp.MustCompile(`(?i)read\s+also:?[^\n]*`),
		regexp.MustCompile(`(?i)related\s+articles:?[^\n]*`),
		regexp.MustCompile(`(?i)share\s+this:?[^\n]*`),
		regexp.MustCompile(`(?i)bagikan:?[^\n]*`),
		regexp.MustCompile(`(?i)subscribe`),
		regexp.MustCompile(`(?i)advertisement`),
		regexp.MustCompile(`(?i)iklan`),
		regexp.MustCompile(`(?i)halaman\s+selanjutnya`),
		regexp.MustCompile(`(?i)sebelumnya`),

		// Specific Greeting/Outro Spam
		regexp.MustCompile(`(?i)tahu\s+gak\s+perseners\??`),
		regexp.MustCompile(`(?i)halo,\s+perseners!`),
		regexp.MustCompile(`(?i)balik\s+lagi\s+sama\s+aku[^\n]*`),
		regexp.MustCompile(`(?i)semoga\s+bermanfaat[^\n]*`),
		regexp.MustCompile(`(?i)terima\s+kasih\s+sudah\s+membaca[^\n]*`),

		// ADDRESSES & CONTACTS (Strict Removal)
		// Jl. Name No. 123, City, Zip
		regexp.MustCompile(`(?i)Jl\.\s+.*No\.\s*\d+.*`),
		regexp.MustCompile(`(?i)Jalan\s+.*Nomor\s*\d+.*`),
		// Phone/WA: 08xx-xxxx-xxxx or +62
		regexp.MustCompile(`(?i)(?:08\d{2}|62\d{2})[- ]?\d{4}[- ]?\d{4}`),
		regexp.MustCompile(`(?i)Whatsapp.*`),
		// Copyright footer
		regexp.MustCompile(`(?i).*All\s+rights\s+reserved.*`),
		regexp.MustCompile(`(?i).*Copyright.*`),
		regexp.MustCompile(`(?i).*PT\s+.*Indonesia.*`), // Company footer
	}

	for _, re := range spamPatterns {
		content = re.ReplaceAllString(content, "")
	}

	// 2. Remove excessive whitespace (Moved to END to preserve line structure for regex above)
	reSpace := regexp.MustCompile(`\s+`)
	content = reSpace.ReplaceAllString(content, " ")

	return strings.TrimSpace(content)
}
