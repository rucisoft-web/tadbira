const rupiahPenuh = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });

export function rupiah(value) {
    return rupiahPenuh.format(Number(value || 0));
}

/** Rupiah ringkas: Rp 1,2 M, Rp 450 jt. */
export function rupiahRingkas(value) {
    const v = Number(value || 0);
    const abs = Math.abs(v);
    const fmt = (n) => n.toLocaleString('id-ID', { maximumFractionDigits: 1 });
    if (abs >= 1e12) return `Rp ${fmt(v / 1e12)} T`;
    if (abs >= 1e9) return `Rp ${fmt(v / 1e9)} M`;
    if (abs >= 1e6) return `Rp ${fmt(v / 1e6)} jt`;
    if (abs >= 1e3) return `Rp ${fmt(v / 1e3)} rb`;
    return rupiahPenuh.format(v);
}

export function tanggal(value, opts = { day: 'numeric', month: 'short', year: 'numeric' }) {
    if (!value) return '–';
    return new Date(`${String(value).slice(0, 10)}T00:00:00`).toLocaleDateString('id-ID', opts);
}

export function tanggalPanjang(value) {
    return tanggal(value, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
}

export function persen(value, digits = 0) {
    return `${Number(value || 0).toLocaleString('id-ID', { maximumFractionDigits: digits })}%`;
}

export function angka(value) {
    return Number(value || 0).toLocaleString('id-ID');
}

export const warnaKesehatan = {
    baik: { label: 'Sesuai jadwal', bg: 'bg-zamrud-100', text: 'text-zamrud-800', dot: 'bg-zamrud-500', hex: '#10B981' },
    waspada: { label: 'Perlu perhatian', bg: 'bg-emas-100', text: 'text-emas-800', dot: 'bg-emas-500', hex: '#D4A72C' },
    kritis: { label: 'Terlambat', bg: 'bg-orange-100', text: 'text-orange-800', dot: 'bg-orange-500', hex: '#F97316' },
};

// Palet garis berwarna yang sama dengan logo.
export const paletGaris = ['#10B981', '#D4A72C', '#38BDF8', '#F97316', '#A78BFA', '#14B8A6', '#F472B6', '#84CC16'];

export function warnaProyek(project, index = 0) {
    return project?.color || paletGaris[index % paletGaris.length];
}
