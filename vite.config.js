import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/js/tex-mml-chtml.js",
                "resources/js/sweetalert2@11.js",
                "resources/js/alert.js",
                "resources/css/admin/dashboard.css",
                "resources/css/admin/header.css",
                "resources/js/admin/dashboard.js",

                "resources/css/admin/test-assignments/student-detail.css",

                "resources/js/admin/students/index.js",
                "resources/js/admin/students/edit.js",
                "resources/js/admin/students/create.js",

                "resources/js/admin/questions/index.js",
                "resources/js/admin/questions/edit.js",
                "resources/js/admin/questions/create.js",
                "resources/css/admin/questions/index.css",
                "resources/css/admin/questions/create.css",
                "resources/css/admin/questions/edit.css",
                "resources/css/admin/questions/delete.css",

                "resources/css/student/home.css",
                "resources/css/student/results.css",
                "resources/css/student/test.css",
                "resources/js/student/test.js",
            ],
            refresh: true,
        }),
    ],
});
