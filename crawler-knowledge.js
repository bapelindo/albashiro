// CRAWLER KNOWLEDGE - Albashiro
// Collect 3000 premium articles about Mental Health & Islamic Psychology
// Using Ollama AI for strict filtering

import axios from 'axios';
import * as cheerio from 'cheerio';
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

// --- SEED URLS (63 Premium Sources) ---
const SEEDS = [
    // --- 🏥 KESEHATAN MENTAL (Portal Besar - SPECIFIC CATEGORY) ---
    { url: 'https://hellosehat.com/mental/', category: 'mental_health_indo', selector: 'h3.title a' },
    { url: 'https://www.halodoc.com/kesehatan-mental', category: 'mental_health_indo', selector: '.article-card-link' },
    { url: 'https://www.alodokter.com/kesehatan-mental', category: 'mental_health_indo', selector: 'entry-title a' },
    { url: 'https://www.klikdokter.com/topik/kesehatan-mental', category: 'mental_health_indo', selector: '.title a' },

    // --- 🕌 ISLAM & SPIRITUALITAS (Portal Besar) ---
    { url: 'https://islam.nu.or.id/', category: 'islamic_general', selector: '.post-list a' },
    { url: 'https://muhammadiyah.or.id/kategori/artikel/', category: 'islamic_general', selector: '.post-title a' },
    { url: 'https://www.dream.co.id/muslim-lifestyle', category: 'muslim_lifestyle', selector: '.dream-list-content a' },

    // --- 🇺🇸 ENGLISH PREMIUM SOURCES (Mental Health) ---
    { url: 'https://www.psychologytoday.com/us/basics', category: 'mental_health_en', selector: '.topic-list-item a' },
    { url: 'https://www.verywellmind.com/', category: 'mental_health_en', selector: '.card-list .card' },
    { url: 'https://www.healthline.com/health/mental-health', category: 'mental_health_en', selector: '.css-1n7h6ir a' },
    { url: 'https://mysoulrefuge.com/', category: 'islamic_psychology_en', selector: 'article a' },

    // --- 💎 GRADE SSS SOURCES (Trusted Authorities) ---
    { url: 'https://www.apa.org/topics', category: 'mental_health_en', selector: 'main a' },
    { url: 'https://www.nimh.nih.gov/health/topics', category: 'mental_health_en', selector: '.usa-list a' },
    { url: 'https://yayasanpulih.org/artikel/', category: 'mental_health', selector: 'article h2 a' },
    { url: 'https://www.intothelightid.org/artikel/', category: 'suicide_prevention', selector: '.post-title a' },
    { url: 'https://ijcp.org/index.php/ijcp', category: 'psychology_journal', selector: '.article-summary .title a' },
    { url: 'https://buletin.k-pin.org/', category: 'psychology_bulletin', selector: '.entry-title a' },

    // --- 💎 GRADE SSS ISLAMIC (Tazkiyatun Nufus - Spesifik Jiwa) ---
    { url: 'https://rumaysho.com/learning/tazkiyatun-nufus', category: 'islamic_psychology', selector: '.entry-title a' },
    { url: 'https://muslim.or.id/tag/tazkiyatun-nufus', category: 'islamic_psychology', selector: '.entry-title a' },

    // --- 💎 GRADE SSS INDONESIAN MENTAL HEALTH (Dedicated Blogs) ---
    { url: 'https://riliv.co/rilivstory/', category: 'mental_health_indo', selector: '.post-title a' },
    { url: 'https://satupersen.net/blog', category: 'mental_health_indo', selector: '.card-blog a' },
    { url: 'https://pijarpsikologi.org/blog', category: 'mental_health_indo', selector: '.post-title a' },
    { url: 'https://www.ibunda.id/blog', category: 'mental_health_indo', selector: '.blog-title a' },

    // --- 🧠 INDO ISLAMIC PSYCHOLOGY & CONSULTATION ---
    { url: 'https://konseling.id/artikel/', category: 'mental_health_indo', selector: '.post-title a' },
    { url: 'https://www.ruangpsikologi.com/blog', category: 'mental_health_indo', selector: '.blog-card a' },

    // --- 🌟 ADDITIONAL MENTAL HEALTH (INDONESIA) ---
    { url: 'https://www.sehatq.com/artikel/kesehatan-mental', category: 'mental_health_indo', selector: '.article-title a' },
    { url: 'https://www.mitrakeluarga.com/artikel/kesehatan-mental', category: 'mental_health_indo', selector: '.article-link' },
    { url: 'https://www.siloamhospitals.com/informasi-kesehatan/artikel/kesehatan-mental', category: 'mental_health_indo', selector: '.title a' },
    { url: 'https://www.rspondokindah.co.id/artikel/kesehatan-mental', category: 'mental_health_indo', selector: '.post-title a' },

    // --- 🕌 ADDITIONAL ISLAMIC PSYCHOLOGY (INDONESIA) ---
    { url: 'https://islampos.com/category/tazkiyatun-nufus/', category: 'islamic_psychology', selector: '.entry-title a' },
    { url: 'https://www.hidayatullah.com/kajian/tazkiyatun-nufus/', category: 'islamic_psychology', selector: '.post-title a' },
    { url: 'https://www.arrahmah.id/category/tazkiyatun-nufus/', category: 'islamic_psychology', selector: '.entry-title a' },
    { url: 'https://konsultasi.wordpress.com/', category: 'islamic_psychology', selector: '.entry-title a' },

    // --- 🌍 ADDITIONAL ENGLISH MENTAL HEALTH ---
    { url: 'https://www.mind.org.uk/information-support/', category: 'mental_health_en', selector: '.card-title a' },
    { url: 'https://www.mentalhealth.org.uk/explore-mental-health', category: 'mental_health_en', selector: '.article-link' },
    { url: 'https://www.nami.org/About-Mental-Illness', category: 'mental_health_en', selector: '.content-link a' },
    { url: 'https://www.beyondblue.org.au/the-facts', category: 'mental_health_en', selector: '.fact-link' },

    // --- 🕌 ADDITIONAL ISLAMIC PSYCHOLOGY (ENGLISH) ---
    { url: 'https://muslimmatters.org/category/psychology/', category: 'islamic_psychology_en', selector: '.entry-title a' },
    { url: 'https://www.virtualmosque.com/psychology/', category: 'islamic_psychology_en', selector: '.post-title a' },
    { url: 'https://seekersguidance.org/articles/general-spirituality/', category: 'islamic_psychology_en', selector: '.article-title a' },
    { url: 'https://www.islamicity.org/category/psychology/', category: 'islamic_psychology_en', selector: '.title a' },

    // --- 💎 PARENTING & FAMILY (Mental Health Context) ---
    { url: 'https://www.parentingclub.co.id/smart-stories/kesehatan-mental-anak', category: 'mental_health_indo', selector: '.story-title a' },
    { url: 'https://www.popmama.com/big-kid/10-12-years-old/kesehatan-mental', category: 'mental_health_indo', selector: '.article__title a' },
    { url: 'https://sahabatkeluarga.kemdikbud.go.id/laman/index.php?r=tpost/xview&id=249900001', category: 'mental_health_indo', selector: '.post-link' },

    // --- 🕌 ISLAMIC PSYCHOLOGY & TAZKIYATUN NUFUS (Jiwa & Hati) ---
    { url: 'https://bincangsyariah.com/khazanah/kesehatan/', category: 'islamic_psychology', selector: '.post-list-item a' },
    { url: 'https://almanhaj.or.id/kategori/tazkiyatun-nufus', category: 'islamic_psychology', selector: '.post-title a' },
    { url: 'https://muslimah.or.id/tag/tazkiyatun-nufus', category: 'islamic_psychology', selector: '.entry-title a' },
    { url: 'https://www.konsultasisyariah.com/tag/kesehatan/', category: 'islamic_general', selector: '.post-title a' },

    // --- 🌍 GLOBAL ISLAMIC PSYCHOLOGY (Grade A++) ---
    { url: 'https://islamicpsychology.org/resources/', category: 'islamic_psychology_en', selector: '.entry-title a' },
    { url: 'https://journal.iamph.org/index.php/jiamph', category: 'islamic_psychology_en', selector: '.title a' },
    { url: 'https://yaqeeninstitute.org/read/psychology', category: 'islamic_psychology_en', selector: '.card a' },
    { url: 'https://khalilcenter.com/articles/', category: 'islamic_psychology_en', selector: '.post-title a' },

    // --- 🧠 INDO ISLAMIC PSYCHOLOGY & CONSULTATION ---
    { url: 'https://www.eramuslim.com/konsultasi/psikologi', category: 'islamic_psychology', selector: '.jeg_post_title a' },
    { url: 'https://hidayatullah.com/kanal/keluarga', category: 'islamic_psychology', selector: '.post-title a' },
    { url: 'https://yufid.com/tag/tazkiyatun-nufus/', category: 'islamic_psychology', selector: '.entry-title a' },
    { url: 'https://wahdah.or.id/tag/tazkiyatun-nufus/', category: 'islamic_psychology', selector: '.entry-title a' },
    { url: 'https://www.nami.org/About-Mental-Illness/Mental-Health-Conditions', category: 'mental_health_en', selector: '.content-list a' },
    { url: 'https://www.mind.org.uk/information-support/types-of-mental-health-problems/', category: 'mental_health_en', selector: '.block-link' },
    { url: 'https://www.beyondblue.org.au/the-facts', category: 'mental_health_en', selector: '.card-link' },
];

// --- STATE ---
const visited = new Set();
const collectedTitles = new Set();
const articleQueue = [];
const collectedData = [];
let articlesCount = 0;

// Ensure output dir
if (!fs.existsSync(OUTPUT_DIR)) fs.mkdirSync(OUTPUT_DIR, { recursive: true });

// --- HELPERS ---
function sleep(ms) { return new Promise(resolve => setTimeout(resolve, ms)); }

async function fetchPage(url) {
    try {
        const response = await axios.get(url, {
            timeout: 10000,
            headers: { 'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36' }
        });
        return cheerio.load(response.data);
    } catch (e) {
        return null;
    }
}

function isSameDomain(url, baseUrl) {
    try {
        const urlDomain = new URL(url).hostname;
        const baseDomain = new URL(baseUrl).hostname;
        return urlDomain === baseDomain;
    } catch { return false; }
}

function extractContent($) {
    const title = $('h1').first().text().trim() || $('title').text().trim();
    let content = '';

    $('p').each((i, el) => {
        content += $(el).text().trim() + ' ';
        if (content.length > 5000) return false;
    });

    return { title, content: content.trim() };
}

// --- AI JUDGE (OLLAMA - STRICT) ---
async function checkRelevanceLLM(title, contentSnippet) {
    try {
        const prompt = `You are a STRICT content filter for a Mental Health & Islamic Psychology database.

ACCEPT ONLY IF:
- Clinical Mental Health: Depression, Anxiety, PTSD, Therapy, Counseling, Grief, Trauma, Bipolar, Schizophrenia, OCD, Phobia, Panic Disorder
- Psychotherapy: CBT, Psychoanalysis, Family Therapy, Group Therapy, Art Therapy, Music Therapy
- Relationship Psychology: Codependency, Attachment, Marriage Counseling, Parenting, Divorce Recovery
- Addiction & Recovery: Substance abuse, Behavioral addiction, Recovery programs
- Self-Development (Clinical): Emotional Intelligence, Stress Management, Mindfulness, Meditation
- Islamic Psychology: Tazkiyatun Nufus, Spiritual Healing, Islamic Counseling, Ruqyah, Taubat, Sabar, Syukur
- Islamic Spirituality: Akhlak, Tasawuf, Zikir, Doa, Muhasabah, Tawakkal, Ikhlas
- Islamic Mental Health: Islamic approach to depression, anxiety, grief in Islamic perspective

REJECT IF:
- Physical Health: BMI, Pregnancy, Diet, Fitness, Medicine, Disease, Nutrition
- Tools/Calculators: BMI calculator, pregnancy calculator, health calculators
- News, Politics, Crime, Gossip, Business, Sports
- Pop Psychology: Clickbait listicles like "7 Ciri...", "5 Bahaya...", "10 Tanda..."
- Entertainment: Celebrity gossip, lifestyle trends, fashion

Title: "${title}"
Content: "${contentSnippet.substring(0, 1000)}"

Reply ONLY: YES or NO`;

        const response = await axios.post('http://localhost:11434/api/chat', {
            model: "crawler",
            messages: [{ role: "user", content: prompt }],
            stream: false,
            options: {
                temperature: 0.0,
                num_ctx: 1024
            }
        }, {
            timeout: 30000
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

    const csvRows = [['category', 'source', 'title', 'url', 'content']];
    collectedData.forEach(item => {
        csvRows.push([
            item.category,
            new URL(item.url).hostname,
            item.title,
            item.url,
            item.content
        ]);
    });

    const csvContent = csvRows.map(row =>
        row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(',')
    ).join('\n');

    fs.writeFileSync(CSV_FILE, csvContent);
}

async function harvestLinks(seedUrl, category, selector) {
    const $ = await fetchPage(seedUrl);
    if (!$) return;

    $(selector).each((i, el) => {
        let href = $(el).attr('href');
        if (!href) return;

        if (!href.startsWith('http')) {
            href = new URL(href, seedUrl).href;
        }

        if (isSameDomain(href, seedUrl) && !visited.has(href)) {
            articleQueue.push({ url: href, category });
        }
    });
}

async function processArticle(url, category) {
    if (visited.has(url)) return;
    visited.add(url);

    const $ = await fetchPage(url);
    if (!$) return;

    const { title, content } = extractContent($);
    if (!title || content.length < 200) return;
    if (collectedTitles.has(title)) return;

    // AI Filter (Ollama - Strict)
    const isRelevant = await checkRelevanceLLM(title, content);

    if (isRelevant) {
        collectedTitles.add(title);
        collectedData.push({ category, url, title, content });
        articlesCount++;

        console.log(`✅ [${articlesCount}/${MAX_ARTICLES}] ${title.substring(0, 40)}... (${new URL(url).hostname})`);

        if (articlesCount % 10 === 0) saveData();

        return true;
    } else {
        console.log(`⚠️ REJECTED: ${title.substring(0, 40)}... (Not relevant)`);
        return false;
    }
}

async function startCrawler() {
    console.log(`🕷️  STARTING MASSIVE SPIDER: Target ${MAX_ARTICLES} Articles`);

    // Harvest initial links
    for (const seed of SEEDS) {
        await harvestLinks(seed.url, seed.category, seed.selector);
    }

    console.log(`📦 Initial Queue: ${articleQueue.length} links. Starting SPIDER MODE...`);

    // Process articles with concurrency
    while (articlesCount < MAX_ARTICLES && articleQueue.length > 0) {
        const batch = articleQueue.splice(0, CONCURRENCY);
        await Promise.all(batch.map(item => processArticle(item.url, item.category)));
    }

    saveData();
    console.log(`\n✅ CRAWLER COMPLETE! Collected ${articlesCount} articles.`);
    console.log(`📁 Saved to: ${DATA_FILE}`);
}

startCrawler();
