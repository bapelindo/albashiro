<?php
/**
 * Albashiro - Pages Controller
 * Handles separate pages: About, Services, Therapists, Contact, Blog
 */

class Pages extends Controller
{

    private $therapistModel;
    private $serviceModel;
    private $testimonialModel;
    private $faqModel;
    private $blogModel;
    private $galleryModel;
    private $galleryCategoryModel;

    public function __construct()
    {
        $this->therapistModel = $this->model('Therapist');
        $this->serviceModel = $this->model('Service');
        $this->testimonialModel = $this->model('Testimonial');
        $this->faqModel = $this->model('Faq');
        $this->blogModel = $this->model('BlogPost');
        $this->galleryModel = $this->model('Gallery');
        $this->galleryCategoryModel = $this->model('GalleryCategory');
    }

    /**
     * About page
     */
    public function tentang()
    {
        $data = [
            'title' => 'Tentang Kami',
            'therapists' => $this->therapistModel->getAll(true),
            'testimonials' => $this->testimonialModel->getFeatured(6),
            'faqs' => $this->faqModel->getAll()
        ];
        echo $this->view('pages/tentang', $data);
    }

    /**
     * Services page
     */
    public function layanan()
    {
        $data = [
            'title' => 'Layanan',
            'services' => $this->serviceModel->getAll(),
            'groupedServices' => $this->serviceModel->getGroupedByAudience()
        ];
        echo $this->view('pages/layanan', $data);
    }

    /**
     * Therapists page
     */
    public function terapis()
    {
        $data = [
            'title' => 'Terapis Kami',
            'therapists' => $this->therapistModel->getAll(true)
        ];
        echo $this->view('pages/terapis', $data);
    }

    /**
     * Gallery page
     */
    public function galeri()
    {
        $categoryId = isset($_GET['kategori']) ? (int) $_GET['kategori'] : null;
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = 18; // 18 is divisible by 2 and 3 for grid layouts

        $galleries = [];
        $categories = [];
        $totalPages = 1;

        try {
            // Get Categories
            $categories = $this->galleryCategoryModel->getAll();

            // Get Pagination Data
            $totalItems = $this->galleryModel->countAll($categoryId);
            $totalPages = ceil($totalItems / $limit);

            // Validate Page
            $page = max(1, min($page, $totalPages > 0 ? $totalPages : 1));

            // Fetch Items
            $galleries = $this->galleryModel->getPaginated($categoryId, $page, $limit);

        } catch (Exception $e) {
            // Tables might not exist yet, ignore error and show empty gallery
        }

        $data = [
            'title' => 'Galeri',
            'galleries' => $galleries,
            'categories' => $categories,
            'currentCategory' => $categoryId,
            'page' => $page,
            'totalPages' => $totalPages
        ];
        echo $this->view('pages/gallery', $data);
    }

    /**
     * Contact page
     */
    public function kontak()
    {
        $data = [
            'title' => 'Kontak'
        ];
        echo $this->view('pages/kontak', $data);
    }

    /**
     * Reservation page
     */
    public function reservasi()
    {
        $data = [
            'title' => 'Reservasi',
            'therapists' => $this->therapistModel->getAll(),
            'services' => $this->serviceModel->getAll(),
            'flash' => $this->getFlash()
        ];
        echo $this->view('pages/reservasi', $data);
    }

    /**
     * Blog list page
     */
    public function blog($slug = null)
    {
        if ($slug) {
            // Single blog post
            $post = $this->blogModel->getBySlug($slug);
            if (!$post) {
                redirect('blog');
            }

            $this->blogModel->incrementViews($post->id);

            $data = [
                'title' => $post->title,
                'post' => $post,
                'recentPosts' => $this->blogModel->getRecent(3)
            ];
            echo $this->view('pages/blog-single', $data);
        } else {
            // Blog listing with pagination, tag filter, and search
            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            $tag = isset($_GET['tag']) ? trim($_GET['tag']) : null;
            $search = isset($_GET['search']) ? trim($_GET['search']) : null;
            $perPage = 9; // 3x3 grid
            $offset = ($page - 1) * $perPage;

            // Get posts (filtered by tag and/or search if provided)
            if ($search) {
                $posts = $this->blogModel->searchPublished($search, $perPage, $offset);
                $totalPosts = $this->blogModel->countSearchPublished($search);
            } elseif ($tag) {
                $posts = $this->blogModel->getPublishedByTag($tag, $perPage, $offset);
                $totalPosts = $this->blogModel->countPublishedByTag($tag);
            } else {
                $posts = $this->blogModel->getPublished($perPage, $offset);
                $totalPosts = $this->blogModel->countPublished();
            }

            $totalPages = ceil($totalPosts / $perPage);

            $data = [
                'title' => 'Blog',
                'posts' => $posts,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'perPage' => $perPage,
                'totalPosts' => $totalPosts,
                'currentTag' => $tag,
                'searchQuery' => $search
            ];
            echo $this->view('pages/blog', $data);
        }
    }
}
