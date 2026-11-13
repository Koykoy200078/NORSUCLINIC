// The asset build (upgrade plan U4: Laravel Mix -> Vite). It does what webpack.mix.js did, with Vite 8 and plain Node:
//
//   node build-assets.mjs                  development build (npm run dev)
//   node build-assets.mjs --production     production build: the bundles, the CSS and the joined files minified (npm run prod)
//   node build-assets.mjs --watch          development build, then again after every change under resources/ (npm run watch)
//   ... --out=<folder>                     write into <folder> instead of public/ (to compare two builds)
//
// What stays as it was under Mix (proven in the spike U4.0, see docs/laravel-13-upgrade-plan.md):
// - every output keeps its path and is a CLASSIC script or stylesheet: the layouts load third-party.js, then pages.js, then
//   realtime.js as blocking scripts and 57 inline blocks call their helpers while the page is parsed, so the six bundles are
//   built as IIFE (one Vite build each: Vite cannot write several IIFE files from one build), never as deferred modules;
// - the copies and the joined theme files are written exactly as Mix wrote them (each file read as UTF-8, joined with "\n");
// - public/mix-manifest.json in Mix's format ("/js/pages.js" => "/js/pages.js?id=<md5>"), so the framework's mix() helper and
//   the views do not change;
// - no development server and no public/hot file: the clinic PC never runs Node, it gets the built files (offline bundle).
import crypto from 'node:crypto';
import fs from 'node:fs';
import { createRequire } from 'node:module';
import path from 'node:path';
import { build, minify } from 'vite';
import tailwind from '@tailwindcss/postcss';

const production = process.argv.includes('--production');
const watch = process.argv.includes('--watch');
const outDir = (process.argv.find((argument) => argument.startsWith('--out=')) || '--out=public').slice('--out='.length);

// Lightning CSS is the CSS minifier Vite itself uses; it is read through Vite so no package is added for it
const { transform: lightningTransform } = createRequire(import.meta.resolve('vite'))('lightningcss');

/** the folders a build replaces (as under Mix; never public/uploads, public/webfonts or public/messages.js, which git tracks) */
const CLEAN = ['assets', 'backend', 'css', 'fonts', 'js', 'vendor', 'web', 'mix-manifest.json'];

/** [source folder, destination folder]: the folder's contents are copied */
const COPY_DIRECTORIES = [
    ['resources/assets/images', 'assets/image'],
    ['resources/assets/front/vendor/font-awesome/webfonts', 'assets/webfonts'],
    ['resources/assets/front/vendor/font-awesome/webfonts', 'webfonts'],
    ['resources/assets/web/plugins/global/fonts', 'assets/css/fonts'],
    ['resources/assets/backend', 'backend'],
    ['resources/assets/fonts', 'fonts'],
    ['resources/assets/web', 'web'],
    ['resources/assets/front', 'assets/front'],
];

/** [source file, destination file] */
const COPY_FILES = [
    ['resources/css/montserrat.css', 'css/montserrat.css'],
    ['resources/css/poppins.css', 'css/poppins.css'],
    ['resources/css/flatpickr-month-select.css', 'css/flatpickr-month-select.css'],
    ['resources/assets/front/vendor/font-awesome/css/all.min.css', 'css/fontawesome.all.min.css'],
    ['node_modules/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css', 'assets/css/bootstrap-datepicker/bootstrap-datepicker.css'],
    ['node_modules/bootstrap-datepicker/dist/js/bootstrap-datepicker.js', 'assets/js/bootstrap-datepicker/bootstrap-datepicker.js'],
    ['resources/messages.js', 'messages.js'],
    ['resources/assets/front/vendor/bootstrap.bundle.min.js', 'assets/front/vendor/bootstrap.bundle.min.js'],
    ['resources/css/ajax-loader.gif', 'css/ajax-loader.gif'],
];

/**
 * [source files, destination]: joined in this order (Mix's styles() / scripts()). A source that starts with "@out/" is read from
 * what this build has already written (the copies above), as Mix read it from public/.
 */
const JOINED = [
    [['resources/theme/css/third-party.css', 'node_modules/quill/dist/quill.snow.css', 'node_modules/quill/dist/quill.bubble.css'], 'assets/css/third-party.css'],
    [['resources/theme/css/style.css'], 'assets/css/style.css'],
    [['resources/theme/css/plugins.css'], 'css/plugins.css'],
    [['resources/theme/css/style.dark.css'], 'assets/css/style-dark.css'],
    [['resources/theme/css/plugins.dark.css'], 'css/plugins.dark.css'],
    [['@out/assets/front/vendor/bootstrap.min.css', '@out/assets/front/vendor/slick.css', '@out/assets/front/vendor/slick-theme.css'], 'css/front-third-party.css'],
    [['resources/theme/js/vendor.js', 'resources/theme/js/plugins.js', 'resources/messages.js', 'node_modules/apexcharts/dist/apexcharts.min.js', 'node_modules/quill/dist/quill.js'], 'js/third-party.js'],
    [[
        'node_modules/jquery/dist/jquery.js',
        'resources/theme/js/vendor.js',
        'resources/theme/js/plugins.js',
        '@out/messages.js',
        'node_modules/apexcharts/dist/apexcharts.min.js',
        '@out/assets/front/vendor/magnific-popup/jquery.magnific-popup.js',
        '@out/assets/front/vendor/bootstrap.bundle.min.js',
        '@out/assets/front/vendor/bootstrap-select/js/bootstrap-select.min.js',
        '@out/assets/js/bootstrap-datepicker/bootstrap-datepicker.js',
        '@out/assets/front/vendor/slick.min.js',
        '@out/assets/front/js/contact.js',
        'resources/assets/js/custom/helper.js',
        'node_modules/quill/dist/quill.js',
    ], 'js/front-third-party.js'],
    [['resources/assets/js/auto_fill/auto_fill.js'], 'assets/js/auto_fill/auto_fill.js'],
];

/** [Sass file, destination] */
const SASS = [
    ['resources/assets/scss/custom-pages-dark.scss', 'assets/css/custom-pages-dark.css'],
    ['resources/assets/scss/pages.scss', 'assets/css/pages.css'],
    ['resources/assets/scss/bill-pdf.scss', 'assets/css/bill-pdf.css'],
    ['resources/assets/scss/prescription-pdf.scss', 'assets/css/prescription-pdf.css'],
    ['resources/assets/front/scss/front-custom.scss', 'assets/front/css/front-custom.css'],
    ['resources/assets/front/scss/about.scss', 'assets/front/css/about.css'],
    ['resources/assets/front/scss/main.scss', 'css/front-pages.css'],
];

/** [Tailwind entry, destination] */
const TAILWIND = [['resources/css/app.css', 'css/app.css']];

/** destination => its source files, run in this order (Mix's js()) */
const BUNDLES = {
    'js/pages.js': [
        'resources/assets/js/custom/helper.js',
        'resources/assets/js/custom/custom.js',
        'resources/assets/js/custom/number-format.js',
        'resources/assets/js/custom/sidebar_menu.js',
        'resources/assets/js/profile/create-edit.js',
        'resources/assets/js/doctors/doctors.js',
        'resources/assets/js/doctors/create-edit.js',
        'resources/assets/js/users/user-profile.js',
        'resources/assets/js/patients/patients.js',
        'resources/assets/js/patients/create-edit.js',
        'resources/assets/js/countries/countries.js',
        'resources/assets/js/states/states.js',
        'resources/assets/js/cities/cities.js',
        'resources/assets/js/barangays/barangays.js',
        'resources/assets/js/specializations/specializations.js',
        'resources/assets/js/roles/roles.js',
        'resources/assets/js/settings/settings.js',
        'resources/assets/js/staff/staff.js',
        'resources/assets/js/staff/create-edit.js',
        'resources/assets/js/dashboard/dashboard.js',
        'resources/assets/js/custom/ph-phone-input.js',
        'resources/assets/js/fronts/sliders/slider.js',
        'resources/assets/js/fronts/sliders/create-edit-slider.js',
        'resources/assets/js/fronts/cms/create.js',
        'resources/assets/js/fronts/front_home/front-home.js',
        'resources/assets/js/reviews/review.js',
        'resources/assets/front/js/front-language.js',
        'resources/assets/js/custom/create-account.js',
        'resources/assets/js/category/category.js',
        'resources/assets/js/generics/generics.js',
        'resources/assets/js/brands/brands.js',
        'resources/assets/js/medicines/medicines.js',
        'resources/assets/js/medicine-availability/medicine-availability.js',
        'resources/assets/js/medicine_history/medicine_bill.js',
        'resources/assets/js/prescriptions/create-edit.js',
        'resources/assets/js/prescriptions/prescriptions.js',
    ],
    'js/front-pages.js': [
        'resources/assets/js/custom/helper.js',
        'resources/assets/js/fronts/sliders/slider.js',
        'resources/assets/front/js/front-custom.js',
        'resources/assets/js/fronts/sliders/create-edit-slider.js',
        'resources/assets/js/fronts/cms/create.js',
        'resources/assets/js/fronts/front_home/front-home.js',
        'resources/assets/front/js/front-language.js',
    ],
    'js/realtime.js': ['resources/assets/js/realtime/realtime.js'],
    'js/app.js': ['resources/js/app.js'],
    'js/custom-auth.js': ['resources/assets/js/custom/custom-auth.js'],
    'js/queue-board.js': ['resources/assets/js/realtime/queue-board.js'],
};

const out = (file) => path.join(outDir, file);

/** @param {string} source */
const sourcePath = (source) => (source.startsWith('@out/') ? out(source.slice('@out/'.length)) : source);

function write(file, contents, written) {
    fs.mkdirSync(path.dirname(out(file)), { recursive: true });
    fs.writeFileSync(out(file), contents);
    written.add(file);
}

/** Vite options shared by every build: nothing written by Vite itself, no public/ copy, no dev-server leftovers. */
function viteOptions(extra) {
    return {
        configFile: false,
        logLevel: 'warn',
        publicDir: false,
        mode: production ? 'production' : 'development',
        ...extra,
        build: { write: false, copyPublicDir: false, reportCompressedSize: false, sourcemap: false, minify: production, cssMinify: production, ...extra.build },
    };
}

/** @returns {Promise<Array<{type: string, fileName: string, code?: string, source?: string|Uint8Array, names?: string[], name?: string}>>} */
async function viteOutput(options) {
    const result = await build(viteOptions(options));
    return (Array.isArray(result) ? result : [result]).flatMap((bundle) => bundle.output);
}

async function buildAll() {
    const started = performance.now();
    const written = new Set();

    for (const target of CLEAN) {
        fs.rmSync(out(target), { recursive: true, force: true });
    }

    for (const [source, destination] of COPY_DIRECTORIES) {
        fs.cpSync(source, out(destination), { recursive: true });
        for (const entry of fs.readdirSync(out(destination), { recursive: true, withFileTypes: true })) {
            if (entry.isFile()) {
                written.add(path.relative(outDir, path.join(entry.parentPath, entry.name)).replace(/\\/g, '/'));
            }
        }
    }
    for (const [source, destination] of COPY_FILES) {
        write(destination, fs.readFileSync(source), written);
    }

    for (const [sources, destination] of JOINED) {
        let contents = sources.map((source) => fs.readFileSync(sourcePath(source)).toString()).join('\n');
        if (production) {
            contents = destination.endsWith('.js')
                ? (await minify(destination, contents, { compress: true, mangle: true })).code
                : Buffer.from(lightningTransform({ filename: destination, code: Buffer.from(contents), minify: true, errorRecovery: true }).code).toString();
        }
        write(destination, contents, written);
    }

    // the Sass files: one Vite build with a CSS-only entry each; the CSS of each entry is written to its destination
    const entryName = (destination) => destination.replace(/\.css$/, '');
    const sassEntries = Object.fromEntries(SASS.map(([source, destination]) => [entryName(destination), path.resolve(source)]));
    await writeCss(await viteOutput({ build: { cssCodeSplit: true, rolldownOptions: { input: sassEntries } } }), written);

    // Tailwind on its own build, so its PostCSS plugin never sees the Sass files; it optimises only for production
    const tailwindEntries = Object.fromEntries(TAILWIND.map(([source, destination]) => [entryName(destination), path.resolve(source)]));
    await writeCss(await viteOutput({ css: { postcss: { plugins: [tailwind({ optimize: production })] } }, build: { cssCodeSplit: true, rolldownOptions: { input: tailwindEntries } } }), written);

    for (const [destination, sources] of Object.entries(BUNDLES)) {
        const entry = '\0bundle:' + destination;
        const output = await viteOutput({
            plugins: [{
                name: 'bundle-entry',
                resolveId: (id) => (id === entry ? id : null),
                load: (id) => (id === entry ? sources.map((source) => `import ${JSON.stringify(path.resolve(source).replace(/\\/g, '/'))};`).join('\n') : null),
            }],
            build: { rolldownOptions: { input: entry, output: { format: 'iife' } } },
        });
        const chunks = output.filter((item) => item.type === 'chunk');
        if (chunks.length !== 1) {
            throw new Error(`${destination}: expected one script, the build made ${chunks.length}`);
        }
        write(destination, chunks[0].code, written);
    }

    const manifest = {};
    for (const file of [...written].sort()) {
        manifest['/' + file] = '/' + file + '?id=' + crypto.createHash('md5').update(fs.readFileSync(out(file))).digest('hex');
    }
    fs.writeFileSync(out('mix-manifest.json'), JSON.stringify(manifest, null, 4));

    console.log(`${production ? 'Production' : 'Development'} build into ${outDir}/: ${written.size} files in ${Math.round(performance.now() - started)} ms`);
}

/** CSS-only entries: every entry must give exactly one stylesheet, written under the entry's name (its destination). */
async function writeCss(output, written) {
    for (const asset of output.filter((item) => item.type === 'asset' && item.fileName.endsWith('.css'))) {
        write(cssDestination(asset), asset.source, written);
    }
}

/** The destination of a CSS asset is the name of the entry it came from: entry "assets/css/pages" gives "assets/css/pages.css". */
function cssDestination(asset) {
    const names = (asset.names && asset.names.length ? asset.names : [asset.name]).filter(Boolean);
    if (names.length !== 1 || !names[0].endsWith('.css')) {
        throw new Error(`a stylesheet that is not one entry's: ${asset.fileName} (${names.join(', ')})`);
    }
    return names[0];
}

await buildAll();

if (watch) {
    let timer = null;
    let running = false;
    console.log('Watching resources/ for changes (Ctrl+C to stop)');
    fs.watch('resources', { recursive: true }, () => {
        clearTimeout(timer);
        timer = setTimeout(async () => {
            if (running) {
                return;
            }
            running = true;
            try {
                await buildAll();
            } catch (error) {
                console.error(error.message);
            } finally {
                running = false;
            }
        }, 300);
    });
}
