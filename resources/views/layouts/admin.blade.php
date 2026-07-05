@extends('layouts.app')

@section('content')
    <div x-data="{ sidebarOpen: true, mobileSidebarOpen: false }" class="min-h-screen flex">
        <x-sidebar />

        <div class="flex-1 flex flex-col min-w-0">
            <x-top-nav />

            <main class="flex-1 p-6">
                <x-breadcrumbs />
                <div class="mt-4">
                    @yield('page-content')
                </div>
            </main>
        </div>
    </div>
    <x-confirm-dialog />

    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <style>
        .ql-source { width: auto !important; }
        .ql-source .ql-source-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 13px;
            font-weight: 500;
            padding: 3px 10px;
            color: #374151;
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            cursor: pointer;
            white-space: nowrap;
        }
        .ql-source .ql-source-btn:hover { background: #e5e7eb; }
        .ql-source .ql-source-btn.active { background: #e0e7ff; color: #4338ca; border-color: #6366f1; }
        .ql-editor-html {
            width: 100%;
            min-height: 250px;
            padding: 12px;
            font-family: 'Menlo', 'Monaco', monospace;
            font-size: 13px;
            line-height: 1.5;
            border: 1px solid #d1d5db;
            border-radius: 0 0 4px 4px;
            resize: vertical;
            display: none;
            outline: none;
        }
        .ql-editor-html:focus { border-color: #6366f1; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const el = document.getElementById('editor');
            if (!el) return;

            const sourceTextarea = document.createElement('textarea');
            sourceTextarea.className = 'ql-editor-html';
            sourceTextarea.placeholder = '{{ __('messages.description') }}...';
            el.parentNode.insertBefore(sourceTextarea, el.nextSibling);

            let isSourceMode = false;

            const quill = new Quill(el, {
                theme: 'snow',
                modules: {
                    toolbar: {
                        container: [
                            ['bold', 'italic', 'underline'],
                            [{ list: 'ordered' }, { list: 'bullet' }],
                            ['link', 'clean'],
                            ['source'],
                        ],
                        handlers: {
                            source: function () {
                                isSourceMode = !isSourceMode;
                                if (isSourceMode) {
                                    sourceTextarea.value = quill.root.innerHTML;
                                    el.style.display = 'none';
                                    sourceTextarea.style.display = 'block';
                                } else {
                                    quill.root.innerHTML = sourceTextarea.value;
                                    sourceTextarea.style.display = 'none';
                                    el.style.display = 'block';
                                }
                                const btn = document.querySelector('.ql-source-btn');
                                if (btn) btn.classList.toggle('active');
                            },
                        },
                    },
                },
                placeholder: '{{ __('messages.description') }}...',
            });

            const toolbar = quill.getModule('toolbar');
            const sourceBtn = document.createElement('span');
            sourceBtn.className = 'ql-source-btn';
            sourceBtn.textContent = '< >';
            toolbar.container.querySelector('.ql-source').appendChild(sourceBtn);

            const form = el.closest('form');
            const hidden = document.createElement('textarea');
            hidden.name = 'description';
            hidden.style.display = 'none';
            form.appendChild(hidden);

            form.addEventListener('submit', function () {
                if (isSourceMode) {
                    hidden.value = sourceTextarea.value;
                } else {
                    hidden.value = quill.root.innerHTML;
                }
            });

            if (el.dataset.value) {
                quill.root.innerHTML = el.dataset.value;
            }
        });
    </script>
@endsection
