
import axios from 'axios';
import * as cheerio from 'cheerio'; // Namespace import for cheerio
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// --- CONFIGURATION ---
const MAX_ARTICLES = 3000; // Target 3000 artikel premium
const CONCURRENCY = 1; // iGPU AMD: 1 concurrent (low VRAM mode)
const OUTPUT_DIR = path.join(__dirname, 'scraped_data');
const DATA_FILE = path.join(OUTPUT_DIR, 'massive_knowledge.json');
const CSV_FILE = path.join(OUTPUT_DIR, 'massive_knowledge.csv');

// --- OLLAMA CONFIGURATION (Local AI) ---
const OLLAMA_BASE_URL = 'http://localhost:11434';
const OLLAMA_MODEL = 'albashiro'; // Model lokal yang sudah ada
const USE_AI_FILTER = true; // Set false untuk disable AI filtering

// --- OLLAMA HELPER FUNCTION ---
async function checkRelevanceWithAI(title, contentSnippet) {
    if (!USE_AI_FILTER) return true; // Skip AI if disabled

    try {
        const prompt = `Classify this article as RELEVANT or NOT RELEVANT.

ACCEPT (say YES) ONLY for:
- Clinical mental health: Depression, Anxiety, PTSD, Therapy, Counseling, Psychological disorders
- Islamic psychology: Tazkiyatun Nufus, Spiritual Healing, Ruqyah, Islamic spirituality

REJECT (say NO) for:
- Physical health: Diet, Fitness, Pregnancy, Medicine, Beauty, Skin care
- News: Politics, Crime, Business, Sports, Entertainment
- Other: Animals, Travel, Cooking, Technology

Title: "${title}"
Content Preview: "${contentSnippet.substring(0, 500)}"

Answer with ONLY: YES or NO`;

        const response = await axios.post(`${OLLAMA_BASE_URL}/api/generate`, {
            model: OLLAMA_MODEL,
            prompt: prompt,
            stream: false,
            options: {
                temperature: 0.1,
                num_predict: 10
            }
        });

        const answer = response.data.response.toUpperCase().trim();
        const isRelevant = answer.includes('YES') && !answer.includes('NO');

        if (!isRelevant) {
            console.log(`      ⚠️ AI REJECTED: ${title.substring(0, 50)}...`);
        }

        return isRelevant;
    } catch (error) {
        console.error(`      ⚠️ AI Error: ${error.message}`);
        return true; // Default to accept if AI fails
    }
}

// --- SEED URLS (Starting Points) ---
const SEEDS = [
    // --- 🏥 KESEHATAN MENTAL (Portal Besar - SPECIFIC CATEGORY) ---
    { url: 'https://hellosehat.com/mental/', category: 'mental_health_indo', selector: 'h3.title a' }, // HelloSehat Mental Only
    { url: 'https://www.halodoc.com/kesehatan-mental', category: 'mental_health_indo', selector: '.article-card-link' }, // Halodoc Mental Only
    { url: 'https://www.alodokter.com/kesehatan-mental', category: 'mental_health_indo', selector: 'entry-title a' }, // Alodokter Mental Only
    { url: 'https://www.klikdokter.com/topik/kesehatan-mental', category: 'mental_health_indo', selector: '.title a' }, // KlikDokter Mental Only
    // { url: 'https://health.kompas.com/mental-health', category: 'mental_health_news', selector: '.article__link' }, // REMOVED: Too much news
    // { url: 'https://health.detik.com/mental-health', category: 'mental_health_news', selector: '.media__link' }, // REMOVED: Too much news
    // { url: 'https://www.cnnindonesia.com/gaya-hidup/kesehatan', category: 'health_news', selector: '.list_content article a' }, // REMOVED: Too much news

    // --- 🕌 ISLAM & SPIRITUALITAS (Portal Besar) ---
    { url: 'https://islam.nu.or.id/', category: 'islamic_general', selector: '.post-list a' },
    { url: 'https://muhammadiyah.or.id/kategori/artikel/', category: 'islamic_general', selector: '.post-title a' },
    // { url: 'https://republika.co.id/kanal/khazanah', category: 'islamic_news', selector: '.title a' }, // REMOVED: Politics mixed in
    { url: 'https://www.dream.co.id/muslim-lifestyle', category: 'muslim_lifestyle', selector: '.dream-list-content a' },

    // --- 🇺🇸 ENGLISH PREMIUM SOURCES (Mental Health) ---
    { url: 'https://www.psychologytoday.com/us/basics', category: 'mental_health_en', selector: '.topic-list-item a' },
    { url: 'https://www.verywellmind.com/', category: 'mental_health_en', selector: '.card-list .card' },
    { url: 'https://www.healthline.com/health/mental-health', category: 'mental_health_en', selector: '.css-1n7h6 ir a' },
    { url: 'https://mysoulrefuge.com/', category: 'islamic_psychology_en', selector: 'article a' }, // Islamic Mental Health Blog

    // --- 💎 GRADE SSS SOURCES (Trusted Authorities) ---
    { url: 'https://www.apa.org/topics', category: 'mental_health_en', selector: 'main a' }, // American Psychological Association
    { url: 'https://www.nimh.nih.gov/health/topics', category: 'mental_health_en', selector: '.usa-list a' }, // National Institute of Mental Health
    { url: 'https://yayasanpulih.org/artikel/', category: 'mental_health', selector: 'article h2 a' }, // Yayasan Pulih (Indo Expert)
    { url: 'https://www.intothelightid.org/artikel/', category: 'suicide_prevention', selector: '.post-title a' }, // Into The Light (Prevention)
    { url: 'https://ijcp.org/index.php/ijcp', category: 'psychology_journal', selector: '.article-summary .title a' }, // Indonesian Journal of Clinical Psychology
    { url: 'https://buletin.k-pin.org/', category: 'psychology_bulletin', selector: '.entry-title a' }, // Konsorsium Psikologi Ilmiah Nusantara
    // --- 💎 GRADE SSS ISLAMIC (Tazkiyatun Nufus - Spesifik Jiwa) ---
    { url: 'https://rumaysho.com/learning/tazkiyatun-nufus', category: 'islamic_psychology', selector: '.entry-title a' }, // Rumaysho (Jiwa)
    { url: 'https://muslim.or.id/tag/tazkiyatun-nufus', category: 'islamic_psychology', selector: '.entry-title a' }, // Muslim.or.id (Jiwa)

    // --- 💎 GRADE SSS INDONESIAN MENTAL HEALTH (Dedicated Blogs) ---
    { url: 'https://riliv.co/rilivstory/', category: 'mental_health_indo', selector: '.post-title a' }, // Riliv (App Meditasi No.1)
    { url: 'https://satupersen.net/blog', category: 'mental_health_indo', selector: '.card-blog a' }, // Satu Persen (Life School)
    { url: 'https://pijarpsikologi.org/blog', category: 'mental_health_indo', selector: '.post-title a' }, // Pijar Psikologi
    { url: 'https://www.ibunda.id/blog', category: 'mental_health_indo', selector: '.blog-title a' }, // Ibunda

    // --- 🧠 INDO ISLAMIC PSYCHOLOGY & CONSULTATION ---
    { url: 'https://konseling.id/artikel/', category: 'mental_health_indo', selector: '.post-title a' }, // Konseling Indonesia
    { url: 'https://www.ruangpsikologi.com/blog', category: 'mental_health_indo', selector: '.blog-card a' }, // Ruang Psikologi

    // --- 🌟 ADDITIONAL MENTAL HEALTH (INDONESIA) ---
    { url: 'https://www.sehatq.com/artikel/kesehatan-mental', category: 'mental_health_indo', selector: '.article-title a' }, // SehatQ Mental
    { url: 'https://www.mitrakeluarga.com/artikel/kesehatan-mental', category: 'mental_health_indo', selector: '.article-link' }, // Mitra Keluarga
    { url: 'https://www.siloamhospitals.com/informasi-kesehatan/artikel/kesehatan-mental', category: 'mental_health_indo', selector: '.title a' }, // Siloam
    { url: 'https://www.rspondokindah.co.id/artikel/kesehatan-mental', category: 'mental_health_indo', selector: '.post-title a' }, // Pondok Indah

    // --- 🕌 ADDITIONAL ISLAMIC PSYCHOLOGY (INDONESIA) ---
    { url: 'https://islampos.com/category/tazkiyatun-nufus/', category: 'islamic_psychology', selector: '.entry-title a' }, // Islam Pos
    { url: 'https://www.hidayatullah.com/kajian/tazkiyatun-nufus/', category: 'islamic_psychology', selector: '.post-title a' }, // Hidayatullah
    { url: 'https://www.arrahmah.id/category/tazkiyatun-nufus/', category: 'islamic_psychology', selector: '.entry-title a' }, // Arrahmah
    { url: 'https://konsultasi.wordpress.com/', category: 'islamic_psychology', selector: '.entry-title a' }, // Konsultasi Islam

    // --- 🌍 ADDITIONAL ENGLISH MENTAL HEALTH ---
    { url: 'https://www.mind.org.uk/information-support/', category: 'mental_health_en', selector: '.card-title a' }, // Mind UK
    { url: 'https://www.mentalhealth.org.uk/explore-mental-health', category: 'mental_health_en', selector: '.article-link' }, // Mental Health Foundation
    { url: 'https://www.nami.org/About-Mental-Illness', category: 'mental_health_en', selector: '.content-link a' }, // NAMI
    { url: 'https://www.beyondblue.org.au/the-facts', category: 'mental_health_en', selector: '.fact-link' }, // Beyond Blue

    // --- 🕌 ADDITIONAL ISLAMIC PSYCHOLOGY (ENGLISH) ---
    { url: 'https://muslimmatters.org/category/psychology/', category: 'islamic_psychology_en', selector: '.entry-title a' }, // Muslim Matters
    { url: 'https://www.virtualmosque.com/psychology/', category: 'islamic_psychology_en', selector: '.post-title a' }, // Virtual Mosque
    { url: 'https://seekersguidance.org/articles/general-spirituality/', category: 'islamic_psychology_en', selector: '.article-title a' }, // SeekersGuidance
    { url: 'https://www.islamicity.org/category/psychology/', category: 'islamic_psychology_en', selector: '.title a' }, // IslamiCity

    // --- 💎 PARENTING & FAMILY (Mental Health Context) ---
    { url: 'https://www.parentingclub.co.id/smart-stories/kesehatan-mental-anak', category: 'mental_health_indo', selector: '.story-title a' }, // Parenting Club
    { url: 'https://www.popmama.com/big-kid/10-12-years-old/kesehatan-mental', category: 'mental_health_indo', selector: '.article__title a' }, // PopMama
    { url: 'https://sahabatkeluarga.kemdikbud.go.id/laman/index.php?r=tpost/xview&id=249900001', category: 'mental_health_indo', selector: '.post-link' }, // Sahabat Keluarga Kemendikbud

    // --- 🕌 ISLAMIC PSYCHOLOGY & TAZKIYATUN NUFUS (Jiwa & Hati) ---
    { url: 'https://bincangsyariah.com/khazanah/kesehatan/', category: 'islamic_psychology', selector: '.post-list-item a' }, // Bincang Syariah (Kesehatan Mental Islam)
    { url: 'https://almanhaj.or.id/kategori/tazkiyatun-nufus', category: 'islamic_psychology', selector: '.post-title a' }, // Almanhaj (Penyucian Jiwa)
    { url: 'https://muslimah.or.id/tag/tazkiyatun-nufus', category: 'islamic_psychology', selector: '.entry-title a' }, // Muslimah (Jiwa)
    { url: 'https://www.konsultasisyariah.com/tag/kesehatan/', category: 'islamic_general', selector: '.post-title a' }, // Konsultasi Syariah

    // --- 🌍 GLOBAL ISLAMIC PSYCHOLOGY (Grade A++) ---
    { url: 'https://islamicpsychology.org/resources/', category: 'islamic_psychology_en', selector: '.entry-title a' }, // IAIP
    { url: 'https://journal.iamph.org/index.php/jiamph', category: 'islamic_psychology_en', selector: '.title a' }, // Journal
    { url: 'https://yaqeeninstitute.org/read/psychology', category: 'islamic_psychology_en', selector: '.card a' }, // Yaqeen Institute (Academic SSS)
    { url: 'https://khalilcenter.com/articles/', category: 'islamic_psychology_en', selector: '.post-title a' }, // Khalil Center (Clinical)

    // --- �� INDO ISLAMIC PSYCHOLOGY & CONSULTATION ---
    { url: 'https://www.eramuslim.com/konsultasi/psikologi', category: 'islamic_psychology', selector: '.jeg_post_title a' }, // Eramuslim Psikologi
    { url: 'https://hidayatullah.com/kanal/keluarga', category: 'islamic_psychology', selector: '.post-title a' }, // Hidayatullah Keluarga
    { url: 'https://yufid.com/tag/tazkiyatun-nufus/', category: 'islamic_psychology', selector: '.entry-title a' }, // Yufid (Jiwa)
    { url: 'https://wahdah.or.id/tag/tazkiyatun-nufus/', category: 'islamic_psychology', selector: '.entry-title a' }, // Wahdah (Jiwa)
    { url: 'https://www.nami.org/About-Mental-Illness/Mental-Health-Conditions', category: 'mental_health_en', selector: '.content-list a' }, // NAMI
    { url: 'https://www.mind.org.uk/information-support/types-of-mental-health-problems/', category: 'mental_health_en', selector: '.block-link' }, // Mind UK
    { url: 'https://www.beyondblue.org.au/the-facts', category: 'mental_health_en', selector: '.card-link' }, // Beyond Blue Australia
];

// --- STATE ---
const visited = new Set();
const collectedTitles = new Set(); // Cegah duplikasi konten via Judul
const articleQueue = []; // Queue of URLs to visit
const collectedData = [];
let articlesCount = 0;

// Ensure output dir
if (!fs.existsSync(OUTPUT_DIR)) fs.mkdirSync(OUTPUT_DIR);

// --- UTILS ---
// --- STEALTH MODE (User Agents) ---
const USER_AGENTS = [
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Safari/605.1.15',
    'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36 Edg/119.0.0.0'
];

function getRandomUserAgent() {
    return USER_AGENTS[Math.floor(Math.random() * USER_AGENTS.length)];
}

const sleep = (ms) => new Promise(r => setTimeout(r, ms));

async function fetchPage(url) {
    try {
        // No Delay - SPEED MODE
        // await randomSleep(1000, 3000); 

        const response = await axios.get(url, {
            headers: {
                'User-Agent': getRandomUserAgent(),
                'Referer': 'https://www.google.com/',
                'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language': 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7'
            },
            timeout: 15000
        });
        return cheerio.load(response.data);
    } catch (error) {
        // console.error(`❌ Error fetching ${url}: ${error.message}`);
        return null;
    }
}

// --- CRAWLER LOGIC ---

// L E B I H   A G R E S I F   C R A W L I N G
// Harvest link bukan cuma dari seed, tapi dari setiap halaman yang dikunjungi (Recursive)
async function harvestLinks(url, category) {
    if (visited.has(url)) return;
    visited.add(url);

    // Safety check biar gak kejauhan
    if (articleQueue.length > MAX_ARTICLES * 2) return;

    try {
        const $ = await fetchPage(url);
        if (!$) return;

        let count = 0;
        $('a').each((i, el) => {
            const href = $(el).attr('href');
            if (!href) return;

            let fullUrl = href;
            try {
                if (href.startsWith('/')) {
                    fullUrl = new URL(url).origin + href;
                } else if (!href.startsWith('http')) {
                    return;
                }
            } catch (e) { return; }

            // Filter Logic:
            // 1. Must be same domain
            // 2. Must likely be an article or category page
            // 3. Not social media share links
            const isSameDomain = fullUrl.includes(new URL(url).hostname);
            const isInteresting = (
                fullUrl.includes('/artikel/') ||
                fullUrl.includes('/baca/') ||
                fullUrl.includes('/post/') ||
                fullUrl.includes('/read/') ||
                fullUrl.includes('/page/') || // Follow pagination!
                fullUrl.includes('kesehatan') ||
                fullUrl.includes('islam') ||
                fullUrl.includes('-')
            );

            if (isSameDomain && isInteresting && !visited.has(fullUrl)) {
                // If it looks like an article page (contains heavy slug), prioritize it
                // If it looks like pagination, add it to harvest queue

                if (fullUrl.length > 50) { // Anggap URL panjang = artikel
                    articleQueue.push({ url: fullUrl, category: category, source_domain: new URL(url).hostname });
                    // visited.add(fullUrl); // Don't mark visited yet, let worker do it
                } else {
                    // Ini mungkin halaman kategori/pagination, kita harvest juga nanti (tapi low priority)
                    // recursive harvest is dangerous, lets just push to queue if queue is running low
                }
                count++;
            }
        });
        // console.log(`   Harvested ${count} links from ${url}`);

    } catch (e) {
        // console.error(e.message); 
    }
}

// Update processArticle to also harvest links from the article itself (Spidering)
async function processArticle(job) {
    if (articlesCount >= MAX_ARTICLES) return;
    if (visited.has(job.url)) return;
    visited.add(job.url);

    const $ = await fetchPage(job.url);
    if (!$) return;

    // --- SPIDER: Cari Link Baru di halaman ini juga ---
    $('a').each((i, el) => {
        const href = $(el).attr('href');

        // Filter URL yang BUKAN berita sampah
        if (href && (href.startsWith('/artikel') || href.includes('/read/') || href.includes('kesehatan') || href.includes('islam'))) {
            let fullUrl = href.startsWith('/') ? 'https://' + job.source_domain + href : href;

            // --- STRICT FILTER ---
            const lowerUrl = fullUrl.toLowerCase();
            // ⚠️ STRICT BLACKLIST (Judul & URL)
            const forbidden = [
                'bisnis', 'ekonomi', 'saham', 'ihsg', 'rupiah', 'dividen',
                'prabowo', 'gibran', 'anies', 'ganjar', 'partai', 'dpr', 'menteri',
                'demo', 'tewas', 'pembunuhan', 'kecelakaan', 'banjir', 'gempa',
                'kriminal', 'tangkap', 'penjara', 'hukum', 'sidang', 'vonis',
                'bola', 'liga', 'olahraga', 'badminton', 'motogp', 'f1',
                'seleb', 'artis', 'film', 'musik', 'konser', 'tiket',
                'wisata', 'kuliner', 'travel', 'zodiak', 'shio', 'ramalan',
                // SPAM / JUNK / NYASAR TEKNIS
                'engineering', 'telecommunication', 'ethanol', 'research', 'journal of',
                'casino', 'slot', 'poker', 'togel', 'gacor', 'zeus', 'pragmatic',
                'iklan', 'advertisement', 'promo', 'diskon', 'jual', 'beli', 'harga',
                // URL BASED FILTER (Q&A / User Content) - REQUEST USER
                '/tanya-dokter/', '/komunitas/', '/forum/', '/diskusi/', '/community/', '/conversation/',
                '/user/', '/profile/', '/auth/', '/login/', '/register/', '/search/'
            ];
            const isForbidden = forbidden.some(word => lowerUrl.includes(word));

            // STRICT DOMAIN CHECK: Jangan sampai keluar dari website asal!
            const isSameDomain = fullUrl.includes(job.source_domain);

            if (!visited.has(fullUrl) && !isForbidden && isSameDomain) {
                articleQueue.push({ url: fullUrl, category: job.category, source_domain: job.source_domain });
            }
        }
    });

    // Validasi Konten
    const title = $('h1').first().text().trim();

    // --- KEYWORD FILTER (Layer 1 - Fast) ---
    const lowerTitle = title.toLowerCase();

    // ⛔ BLACKLIST: Politik, Kriminal, Fisik, Kecantikan
    const badTitleKeywords = [
        // Politics & Crime
        'jokowi', 'prabowo', 'gibran', 'kpk', 'polisi', 'tewas', 'ditangkap', 'korupsi', 'kebakaran', 'banjir',
        'timnas', 'shio', 'zodiak', 'saham', 'rupiah', 'harga emas', 'laba', 'dividen', 'investasi',
        'pilkada', 'pemilu', 'debat', 'kampanye', 'partai', 'koalisi', 'menteri', 'resuffle',
        'kebijakan baru', 'aturan baru', 'tarif', 'cukai', 'pajak', 'subsidi', 'bansos',
        // Disaster & Authorities (New Feedback: "Aparat", "Banjir Bandang")
        'aparat', 'bencana', 'longsor', 'gempa', 'tsunami', 'korban jiwa', 'meninggal dunia', 'tewas',
        'kecelakaan', 'lalu lintas', 'bmkg', 'basarnas', 'bnpb', 'kemenag', 'mui', 'fatwa',
        // Physical Health / Beauty / Gym (New Request)
        'kulit', 'wajah', 'jerawat', 'kecantikan', 'miss v', 'organ intim', 'vagina', 'penis',
        'hamil', 'kehamilan', 'menyusui', 'imunisasi', 'vaksin', 'diabetes', 'jantung', 'kanker',
        'asam urat', 'kolesterol', 'diet', 'berat badan', 'langsing', 'gemuk', 'otot', 'fitness', 'gym',
        'resep', 'masak', 'kuliner', 'wisata',
        // Animal / Pets (User Feedback: "Anjing Shih Tzu", "Cacingan pada Anjing")
        'anjing', 'kucing', 'hewan', 'peliharaan', 'pet', 'veteriner', 'dokter hewan', 'kutu', 'bulu',
        'cacingan', 'rabies', 'infeksi pada anjing', 'infeksi pada kucing', 'sakit pada anjing', 'sakit pada kucing',
        'hamster', 'kelinci', 'burung', 'ikan'
    ];

    // ⛔ BLACKLIST: User Q&A / Konsultasi / Curhat / ADMIN PAGES
    // Jika judulnya seperti orang bertanya ke dokter, UDAH PASTI BUKAN ARTIKEL.
    const qaKeywords = [
        'dok', 'dokter', 'saya', 'aku', 'tanya', 'bertanya', 'mohon', 'tolong', 'solusi',
        'kenapa', 'mengapa', 'apakah', 'bagaimana', 'gimana', 'normalkah', 'wajarkah',
        'hamilkah', 'bisa sembuh', 'obatnya apa', 'resep obat', 'keluhan', 'sakit apa',
        // Admin / Static Pages (User Feedback: "Board of Directors")
        'about us', 'tentang kami', 'redaksi', 'pedoman', 'kebijakan', 'privasi', 'privacy',
        'disclaimer', 'contact', 'hubungi kami', 'board of directors', 'karir', 'lowongan',
        'iklan', 'mediakit', 'sitemap', 'login', 'register', 'signin', 'signup'
    ];

    // Cek apakah judul dimulai dengan kata tanya atau sapaan (Ciri khas Q&A)
    // Atau mengandung kata kunci curhat
    if (qaKeywords.some(w => lowerTitle.includes(' ' + w + ' ') || lowerTitle.startsWith(w + ' '))) {
        // process.stdout.write(`🗣️ User Q&A Rejected: ${title.substring(0, 30)}...\n`);
        return;
    }

    if (badTitleKeywords.some(w => lowerTitle.includes(w))) {
        // process.stdout.write(`⛔ Keyword Rejected: ${title.substring(0, 30)}...\n`);
        return;
    }

    // --- DEDUPLICATION (Title Check) ---
    if (collectedTitles.has(lowerTitle)) {
        return;
    }

    let content = '';

    // Better Content Extraction Strategy
    // Remove unwanted elements first (Clean Comments & Ads)
    $('script, style, iframe, nav, footer, header, .ads, .advertisement, .related-post, .baca-juga, .share-buttons, #comments, .comments, .comment-list, .discussion, .reply').remove();

    const articleBody = $('.article-body, .entry-content, .post-content, article, .read__content, .detail-text').first();

    if (articleBody.length > 0) {
        content = articleBody.text().trim();
    } else {
        $('p').each((i, el) => {
            const text = $(el).text().trim();
            if (text.length > 40) content += text + '\n\n';
        });
    }

    // --- ADVANCED CLEANING ---
    content = content
        .replace(/Baca juga:.*$/gim, '')
        .replace(/Simak video.*$/gim, '')
        .replace(/Download aplikasi.*$/gim, '')
        .replace(/\s+/g, ' ')
        .trim();

    // Syarat: Judul ada, Konten > 300 karakter
    if (title && content.length > 300) {

        // --- 🛡️ SAFETY NET (Whitelist) ---
        // Topik sensitif yang WAJIB LOLOS (Bypass AI Judge)
        const whiteListKeywords = [
            'tafsir', 'surah', 'ayat', 'ibadah', 'akhlak', 'fiqih',
            'pelecehan', 'kekerasan seksual', 'korban', 'pemulihan',
            'patah hati', 'putus cinta', 'jodoh', 'pernikahan', 'suami', 'istri', 'pasangan',
            'baby blues', 'wajib dibaca',
            // Specific Mental Disorders (User Request)
            'depresi', 'anxiety', 'cemas', 'skizofrenia', 'schizophrenia', 'bipolar', 'ocd', 'gangguan',
            'bunuh diri', 'suicide', 'self harm', 'luka batin', 'trauma', 'stress', 'stres',
            'psikolog', 'psikiater', 'jiwa', 'mental',
            // Islamic Healing
            'sholat', 'shalat', 'tahajud', 'puasa', 'zikir', 'dzikir', 'doa', 'ruqyah', 'sabar', 'syukur'
        ];

        const isWhitelisted = whiteListKeywords.some(w => lowerTitle.includes(w));

        if (isWhitelisted) {
            // process.stdout.write(`🛡️ WHITELISTED: ${title.substring(0, 40)}...\n`);
            // Langsung lolos, gak perlu tanya AI (Hemat waktu & Anti salah)
        } else {
            // --- AI JUDGE (Layer 2 - Smart LLM) ---
            // Menggunakan Qwen3 ('albashiro') untuk akurasi tinggi.
            const isRelevant = await checkRelevanceLLM(title, content);

            if (!isRelevant) {
                process.stdout.write(`⚠️ LLM REJECTED: ${title.substring(0, 40)}... (Not relevant)\n`);
                return;
            } else {
                process.stdout.write(`🧠 LLM ACCEPTED: ${title.substring(0, 40)}...\n`);
            }
        }

        collectedTitles.add(lowerTitle); // Mark as collected
        collectedData.push({
            url: job.url,
            category: job.category,
            source: job.source_domain,
            title: title,
            content: content
        });
        articlesCount++;
        process.stdout.write(`✅ [${articlesCount}/${MAX_ARTICLES}] ${title.substring(0, 40)}... (${job.source_domain})\n`);
        if (articlesCount % 20 === 0) saveData();
    }
}

// --- AI JUDGE (OLLAMA LLM) ---
// Menggunakan Model 'crawler' (Qwen3 via Ollama)
// Strict filtering dengan contoh eksplisit
async function checkRelevanceLLM(title, contentSnippet) {
    try {
        const prompt = `You are a STRICT content filter. Classify as RELEVANT or NOT RELEVANT.

ACCEPT (say YES) ONLY for:
- Clinical mental health: Depression, Anxiety, PTSD, Therapy, Counseling, Schizophrenia
- Islamic psychology: Tazkiyatun Nufus, Spiritual Healing, Islamic Counseling, Ruqyah

REJECT (say NO) for:
- Physical health tools: "Kalkulator BMI", "Kalkulator Kehamilan", pregnancy calculators
- Physical health topics: Diet, Fitness, Gym, Medicine, Disease, Pregnancy
- News, Politics, Crime, Gossip, Business
- Pop psychology: Clickbait, listicles, generic self-help

Title: "${title}"
Content: "${contentSnippet.substring(0, 1000)}"

Answer ONLY: YES or NO`;

        const response = await axios.post('http://localhost:11434/api/chat', {
            model: "crawler",
            messages: [{ role: "user", content: prompt }],
            stream: false,
            options: {
                temperature: 0.0,
                num_ctx: 1024
            }
        }, {
            timeout: 30000 // 30 detik (iGPU AMD butuh waktu lebih lama)
        });

        const answer = response.data.message.content.trim().toUpperCase();
        return answer.includes("YES") && !answer.includes("NO");

    } catch (e) {
        console.error(`⚠️ Ollama Error: ${e.message}`);
        return false;
    }
}

function saveData() {
    fs.writeFileSync(DATA_FILE, JSON.stringify(collectedData, null, 2));

    // CSV output
    const header = 'category,source,title,url,content\n';
    const rows = collectedData.map(d => {
        const cleanContent = d.content.replace(/"/g, '""').replace(/\n/g, ' ');
        const cleanTitle = d.title.replace(/"/g, '""');
        return `"${d.category}","${d.source}","${cleanTitle}","${d.url}","${cleanContent}"`;
    }).join('\n');

    fs.writeFileSync(CSV_FILE, header + rows);
}
async function startCrawler() {
    console.log(`🕷️  STARTING MASSIVE SPIDER: Target ${MAX_ARTICLES} Articles`);

    // 1. Initial Harvest
    for (const seed of SEEDS) {
        try {
            await harvestLinks(seed.url, seed.category, seed.selector);
        } catch (e) {
            console.error(`❌ Failed to harvest ${seed.url}: ${e.message}`);
        }
    }

    console.log(`📦 Initial Queue: ${articleQueue.length} links. Starting SPIDER MODE...`);

    // 2. Loop
    while (articlesCount < MAX_ARTICLES) {
        try {
            if (articleQueue.length === 0) {
                console.log("⚠️ Queue empty! Harvesting deeper...");
                break;
            }

            const batch = articleQueue.splice(0, CONCURRENCY);
            // Wrap each job in catch so one failure doesn't kill the batch
            await Promise.all(batch.map(job =>
                processArticle(job).catch(err => console.error(`❌ Error processing ${job.url}: ${err.message}`))
            ));

            if (articleQueue.length < 100) {
                // Optional: Logic to find more links
                // process.stdout.write('🔍 Queue low...\n');
            }

            await sleep(200);
        } catch (error) {
            console.error("🔥 CRITICAL LOOP ERROR:", error);
            await sleep(1000); // Prevent tight loop crash
        }
    }

    saveData();
    console.log(`\n🏁 SPIDER FINISHED! Collected ${articlesCount} articles.`);
}

// Global Error Handlers
process.on('uncaughtException', (err) => {
    console.error('💥 UNCAUGHT EXCEPTION:', err);
});

process.on('unhandledRejection', (reason, p) => {
    console.error('💥 UNHANDLED REJECTION:', reason);
});

startCrawler();
