<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Folder;
use App\Models\Group;
use App\Models\Role;
use App\Models\User;
use App\Models\YouTubeVideo;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Admin', 'description' => 'Administrator with full access'],
        );

        $userRole = Role::firstOrCreate(
            ['slug' => 'user'],
            ['name' => 'User', 'description' => 'Standard user with assigned course access'],
        );

        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'role_id' => $adminRole->id,
                'is_active' => true,
            ],
        );

        $testUser = User::firstOrCreate(
            ['username' => 'user'],
            [
                'name' => 'Test User',
                'email' => 'user@example.com',
                'password' => bcrypt('password'),
                'role_id' => $userRole->id,
                'is_active' => true,
            ],
        );

        $this->call(UserSeeder::class);

        $defaultGroup = Group::firstOrCreate(
            ['name' => 'Students'],
            ['description' => 'Default student group'],
        );

        if (!$defaultGroup->users()->where('user_id', $testUser->id)->exists()) {
            $defaultGroup->users()->attach($testUser);
        }

        $courses = $this->seedCourses($admin);

        foreach ($courses as $course) {
            $this->seedFolders($course);
        }

        $this->seedYouTubeVideos($courses);

        $defaultGroup->courses()->syncWithoutDetaching([
            $courses['web-dev']->id => ['permission' => 'download'],
            $courses['data-science']->id => ['permission' => 'view'],
        ]);
    }

    private function seedCourses(User $admin): array
    {
        $courses = [
            'web-dev' => Course::firstOrCreate(
                ['slug' => 'introduction-to-web-development'],
                [
                    'title' => 'Introduction to Web Development',
                    'description' => 'Learn the fundamentals of web development including HTML, CSS, JavaScript, and modern frameworks. Perfect for beginners who want to build websites from scratch.',
                    'is_published' => true,
                    'show_on_homepage' => true,
                    'sort_order' => 1,
                    'created_by' => $admin->id,
                ],
            ),
            'data-science' => Course::firstOrCreate(
                ['slug' => 'data-science-fundamentals'],
                [
                    'title' => 'Data Science Fundamentals',
                    'description' => 'Explore the world of data science with Python, statistics, and machine learning. Hands-on projects with real-world datasets.',
                    'is_published' => true,
                    'show_on_homepage' => true,
                    'sort_order' => 2,
                    'created_by' => $admin->id,
                ],
            ),
            'mobile-dev' => Course::firstOrCreate(
                ['slug' => 'mobile-app-development'],
                [
                    'title' => 'Mobile App Development',
                    'description' => 'Build native mobile applications for iOS and Android. Covers Swift, Kotlin, and cross-platform frameworks.',
                    'is_published' => false,
                    'show_on_homepage' => false,
                    'sort_order' => 3,
                    'created_by' => $admin->id,
                ],
            ),
        ];

        return $courses;
    }

    private function seedFolders(Course $course): void
    {
        match ($course->slug) {
            'introduction-to-web-development' => $this->seedWebDevFolders($course),
            'data-science-fundamentals' => $this->seedDataScienceFolders($course),
            'mobile-app-development' => $this->seedMobileDevFolders($course),
            default => null,
        };
    }

    private function seedWebDevFolders(Course $course): void
    {
        $htmlCss = Folder::firstOrCreate(
            ['course_id' => $course->id, 'name' => 'HTML & CSS', 'parent_id' => null],
            ['description' => 'Core markup and styling languages', 'sort_order' => 1],
        );

        Folder::firstOrCreate(
            ['course_id' => $course->id, 'name' => 'HTML Basics', 'parent_id' => $htmlCss->id],
            ['description' => 'Tags, attributes, forms, and semantic HTML', 'sort_order' => 1],
        );

        Folder::firstOrCreate(
            ['course_id' => $course->id, 'name' => 'CSS Layouts', 'parent_id' => $htmlCss->id],
            ['description' => 'Flexbox, Grid, positioning, and responsive design', 'sort_order' => 2],
        );

        $js = Folder::firstOrCreate(
            ['course_id' => $course->id, 'name' => 'JavaScript', 'parent_id' => null],
            ['description' => 'Client-side scripting and DOM manipulation', 'sort_order' => 2],
        );

        Folder::firstOrCreate(
            ['course_id' => $course->id, 'name' => 'Variables & Functions', 'parent_id' => $js->id],
            ['description' => 'Data types, scope, closures, and arrow functions', 'sort_order' => 1],
        );

        Folder::firstOrCreate(
            ['course_id' => $course->id, 'name' => 'DOM & Events', 'parent_id' => $js->id],
            ['description' => 'Selecting elements, event listeners, and animations', 'sort_order' => 2],
        );

        Folder::firstOrCreate(
            ['course_id' => $course->id, 'name' => 'Async JavaScript', 'parent_id' => $js->id],
            ['description' => 'Promises, async/await, fetch API, and error handling', 'sort_order' => 3],
        );

        $frameworks = Folder::firstOrCreate(
            ['course_id' => $course->id, 'name' => 'Frameworks', 'parent_id' => null],
            ['description' => 'Modern JavaScript frameworks', 'sort_order' => 3],
        );

        Folder::firstOrCreate(
            ['course_id' => $course->id, 'name' => 'React', 'parent_id' => $frameworks->id],
            ['description' => 'Components, hooks, state management, and routing', 'sort_order' => 1],
        );

        Folder::firstOrCreate(
            ['course_id' => $course->id, 'name' => 'Vue.js', 'parent_id' => $frameworks->id],
            ['description' => 'Reactive components, Vue Router, and Pinia', 'sort_order' => 2],
        );

        Folder::firstOrCreate(
            ['course_id' => $course->id, 'name' => 'Backend Basics', 'parent_id' => null],
            ['description' => 'Server-side concepts, APIs, and databases', 'sort_order' => 4],
        );
    }

    private function seedDataScienceFolders(Course $course): void
    {
        $python = Folder::firstOrCreate(
            ['course_id' => $course->id, 'name' => 'Python for Data Science', 'parent_id' => null],
            ['description' => 'Python programming essentials for data analysis', 'sort_order' => 1],
        );

        Folder::firstOrCreate(
            ['course_id' => $course->id, 'name' => 'NumPy & Pandas', 'parent_id' => $python->id],
            ['description' => 'Numerical computing and data manipulation', 'sort_order' => 1],
        );

        Folder::firstOrCreate(
            ['course_id' => $course->id, 'name' => 'Data Visualization', 'parent_id' => $python->id],
            ['description' => 'Matplotlib, Seaborn, and Plotly', 'sort_order' => 2],
        );

        Folder::firstOrCreate(
            ['course_id' => $course->id, 'name' => 'Statistics', 'parent_id' => null],
            ['description' => 'Descriptive and inferential statistics', 'sort_order' => 2],
        );

        $ml = Folder::firstOrCreate(
            ['course_id' => $course->id, 'name' => 'Machine Learning', 'parent_id' => null],
            ['description' => 'Supervised and unsupervised learning algorithms', 'sort_order' => 3],
        );

        Folder::firstOrCreate(
            ['course_id' => $course->id, 'name' => 'Regression', 'parent_id' => $ml->id],
            ['description' => 'Linear regression, polynomial regression, and metrics', 'sort_order' => 1],
        );

        Folder::firstOrCreate(
            ['course_id' => $course->id, 'name' => 'Classification', 'parent_id' => $ml->id],
            ['description' => 'Logistic regression, decision trees, and SVMs', 'sort_order' => 2],
        );

        Folder::firstOrCreate(
            ['course_id' => $course->id, 'name' => 'Clustering', 'parent_id' => $ml->id],
            ['description' => 'K-means, hierarchical clustering, and DBSCAN', 'sort_order' => 3],
        );
    }

    private function seedMobileDevFolders(Course $course): void
    {
        $ios = Folder::firstOrCreate(
            ['course_id' => $course->id, 'name' => 'iOS Development', 'parent_id' => null],
            ['description' => 'Native iOS app development with Swift and SwiftUI', 'sort_order' => 1],
        );

        Folder::firstOrCreate(
            ['course_id' => $course->id, 'name' => 'Swift Basics', 'parent_id' => $ios->id],
            ['description' => 'Variables, control flow, optionals, and protocols', 'sort_order' => 1],
        );

        Folder::firstOrCreate(
            ['course_id' => $course->id, 'name' => 'SwiftUI', 'parent_id' => $ios->id],
            ['description' => 'Views, state management, navigation, and animations', 'sort_order' => 2],
        );

        Folder::firstOrCreate(
            ['course_id' => $course->id, 'name' => 'App Architecture', 'parent_id' => $ios->id],
            ['description' => 'MVVM, dependency injection, and networking', 'sort_order' => 3],
        );

        $android = Folder::firstOrCreate(
            ['course_id' => $course->id, 'name' => 'Android Development', 'parent_id' => null],
            ['description' => 'Native Android development with Kotlin and Jetpack Compose', 'sort_order' => 2],
        );

        Folder::firstOrCreate(
            ['course_id' => $course->id, 'name' => 'Kotlin Basics', 'parent_id' => $android->id],
            ['description' => 'Null safety, coroutines, and data classes', 'sort_order' => 1],
        );

        Folder::firstOrCreate(
            ['course_id' => $course->id, 'name' => 'Jetpack Compose', 'parent_id' => $android->id],
            ['description' => 'Composable functions, state hoisting, and theming', 'sort_order' => 2],
        );

        Folder::firstOrCreate(
            ['course_id' => $course->id, 'name' => 'Cross-Platform', 'parent_id' => null],
            ['description' => 'Flutter and React Native for cross-platform development', 'sort_order' => 3],
        );
    }

    private function seedYouTubeVideos(array $courses): void
    {
        YouTubeVideo::firstOrCreate(
            ['url' => 'https://www.youtube.com/watch?v=UB1O30fR-EE'],
            [
                'title' => 'HTML Crash Course For Absolute Beginners',
                'youtube_id' => 'UB1O30fR-EE',
                'description' => 'Learn HTML from scratch in this comprehensive beginner tutorial.',
                'course_id' => $courses['web-dev']->id,
                'folder_id' => null,
                'sort_order' => 1,
            ],
        );

        YouTubeVideo::firstOrCreate(
            ['url' => 'https://www.youtube.com/watch?v=hdI2bqOjy3c'],
            [
                'title' => 'JavaScript Crash Course',
                'youtube_id' => 'hdI2bqOjy3c',
                'description' => 'A fast-paced introduction to JavaScript programming.',
                'course_id' => $courses['web-dev']->id,
                'folder_id' => null,
                'sort_order' => 2,
            ],
        );

        $htmlCssFolder = Folder::where('course_id', $courses['web-dev']->id)
            ->where('name', 'CSS Layouts')->first();

        if ($htmlCssFolder) {
            YouTubeVideo::firstOrCreate(
                ['url' => 'https://www.youtube.com/watch?v=3YW65K6LcIA'],
                [
                    'title' => 'CSS Flexbox Tutorial',
                    'youtube_id' => '3YW65K6LcIA',
                    'description' => 'Master CSS Flexbox with practical examples.',
                    'course_id' => $courses['web-dev']->id,
                    'folder_id' => $htmlCssFolder->id,
                    'sort_order' => 1,
                ],
            );
        }

        YouTubeVideo::firstOrCreate(
            ['url' => 'https://www.youtube.com/watch?v=wxz5FJYZs4c'],
            [
                'title' => 'Python for Data Science - Full Course',
                'youtube_id' => 'wxz5FJYZs4c',
                'description' => 'Complete Python course for data science beginners.',
                'course_id' => $courses['data-science']->id,
                'folder_id' => null,
                'sort_order' => 1,
            ],
        );

        YouTubeVideo::firstOrCreate(
            ['url' => 'https://www.youtube.com/watch?v=7eh4d6sabA0'],
            [
                'title' => 'Machine Learning for Beginners',
                'youtube_id' => '7eh4d6sabA0',
                'description' => 'Introduction to machine learning concepts and algorithms.',
                'course_id' => $courses['data-science']->id,
                'folder_id' => null,
                'sort_order' => 2,
            ],
        );
    }
}
