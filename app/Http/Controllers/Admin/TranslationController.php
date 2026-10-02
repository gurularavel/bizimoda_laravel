<?php

namespace App\Http\Controllers\Admin;

use App\Support\Locales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

/**
 * İnterfeys sözlərinin tərcüməsi: lang/{locale}.json faylları.
 * Açar — default dildəki (az) mətndir; boş tərcümə açarın özünü göstərir.
 */
class TranslationController extends Controller
{
    protected function path(string $locale): string
    {
        return lang_path($locale.'.json');
    }

    protected function load(string $locale): array
    {
        $file = $this->path($locale);

        return is_file($file) ? (json_decode(File::get($file), true) ?: []) : [];
    }

    protected function write(string $locale, array $data): void
    {
        File::ensureDirectoryExists(lang_path());
        ksort($data, SORT_NATURAL | SORT_FLAG_CASE);
        File::put($this->path($locale), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");
    }

    public function index(Request $request)
    {
        $locales = Locales::codes();
        $data = [];
        $keys = [];
        foreach ($locales as $locale) {
            $data[$locale] = $this->load($locale);
            $keys = array_merge($keys, array_keys($data[$locale]));
        }
        $keys = array_values(array_unique($keys));
        natcasesort($keys);

        $q = mb_strtolower(trim((string) $request->query('q')));
        $onlyMissing = $request->boolean('missing');
        $keys = array_values(array_filter($keys, function ($key) use ($q, $onlyMissing, $data, $locales) {
            if ($q !== '' && ! str_contains(mb_strtolower($key.' '.implode(' ', array_map(fn ($l) => $data[$l][$key] ?? '', $locales))), $q)) {
                return false;
            }
            if ($onlyMissing) {
                foreach ($locales as $l) {
                    if ($l !== Locales::default() && trim((string) ($data[$l][$key] ?? '')) === '') {
                        return true;
                    }
                }

                return false;
            }

            return true;
        }));

        return view('admin.translations.index', [
            'keys' => $keys,
            'data' => $data,
            'locales' => $locales,
            'default' => Locales::default(),
        ]);
    }

    public function update(Request $request)
    {
        $rows = (array) $request->input('t', []);
        foreach (Locales::codes() as $locale) {
            $data = $this->load($locale);
            foreach ($rows as $encodedKey => $values) {
                $key = base64_decode($encodedKey);
                if (array_key_exists($locale, (array) $values)) {
                    $value = trim((string) $values[$locale]);
                    // Boş dəyər yazılmır: Laravel boş sətri qaytarardı, açar (az mətni) göstərilməlidir
                    if ($value === '' && $locale === Locales::default()) {
                        $data[$key] = $key;
                    } elseif ($value === '') {
                        unset($data[$key]);
                    } else {
                        $data[$key] = $value;
                    }
                }
            }
            $this->write($locale, $data);
        }

        return back()->with('success', 'Tərcümələr yadda saxlanıldı.');
    }

    /** resources/views və app altında __('...') açarlarını tapıb fayllara əlavə edir */
    public function scan()
    {
        $keys = [];
        foreach ([resource_path('views/front'), resource_path('views/emails'), app_path()] as $dir) {
            foreach (File::allFiles($dir) as $file) {
                $content = $file->getContents();
                preg_match_all("/(?:__|trans_choice)\(\s*'((?:[^'\\\\]|\\\\.)+)'/u", $content, $m);
                foreach ($m[1] as $key) {
                    $keys[stripslashes($key)] = true;
                }
            }
        }

        // Siyahı default dilin faylından qurulur; digər dillərə yalnız doldurulmuş tərcümələr yazılır
        $locale = Locales::default();
        $data = $this->load($locale);
        $added = 0;
        foreach (array_keys($keys) as $key) {
            if (! array_key_exists($key, $data)) {
                $data[$key] = $key;
                $added++;
            }
        }
        $this->write($locale, $data);

        return back()->with('success', count($keys).' açar tapıldı, '.$added.' yeni sətir əlavə edildi.');
    }
}
