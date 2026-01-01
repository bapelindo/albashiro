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

// URL patterns to reject
var URLBlacklist = []string{
	"/tanya-dokter/", "/komunitas/", "/forum/", "/diskusi/", "/community/", "/conversation/",
	"/user/", "/profile/", "/auth/", "/login/", "/register/", "/search/", "/tag/", "/category/",
	"/author/", "/page/", "/feed/", "/rss/", "/sitemap/", "/wp-admin/", "/wp-content/",
	"visi-misi", "struktur-", "pimpinan-", "senat-", "dewan-", "kepakaran-",
	"surat-keputusan", "unit-kerja", "agenda", "pengumuman", "lowongan", "karir", "bab-",
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

	// Check blacklist keywords
	combinedText := titleLower + " " + contentLower
	for _, keyword := range BlacklistKeywords {
		if strings.Contains(combinedText, keyword) {
			return false
		}
	}

	// Check whitelist keywords (must have at least one)
	hasWhitelistKeyword := false
	for _, keyword := range WhitelistKeywords {
		if strings.Contains(combinedText, keyword) {
			hasWhitelistKeyword = true
			break
		}
	}

	return hasWhitelistKeyword
}

func CleanContent(content string) string {
	// Remove excessive whitespace
	re := regexp.MustCompile(`\s+`)
	content = re.ReplaceAllString(content, " ")

	// Remove common noise patterns
	noisePatterns := []string{
		"Baca juga:", "Read also:", "Related articles:",
		"Share this:", "Bagikan:", "Subscribe",
		"Advertisement", "Iklan",
	}

	for _, noise := range noisePatterns {
		content = strings.ReplaceAll(content, noise, "")
	}

	return strings.TrimSpace(content)
}
