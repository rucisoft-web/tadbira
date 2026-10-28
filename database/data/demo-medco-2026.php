<?php

/*
 * Data demo portofolio proyek perusahaan energi swasta, TA 2026, berdasarkan Laporan Tahunan 2025
 * PT Medco Energi Internasional Tbk.
 *
 * Nama aset, proyek, dan target 2026 (produksi 165–170 mboepd, penjualan listrik ±4.550 GWh,
 * Bualuang Fase I Q2-2026, Sakakemang Q3-2027, pengembangan Sambar, dan seterusnya) diambil dari
 * laporan tahunan. Anggaran per proyek, jadwal, kemajuan, realisasi, dan capaian KPI per Oktober
 * 2026 adalah karangan untuk demo. Nama perusahaan dipakai sebagai studi kasus.
 *
 * Proyek: [nama, jenis, kode sasaran, anggaran dalam juta AS$, sumber dana]
 * Anggaran dikonversi ke rupiah dengan kurs Rp16.500/AS$ (multiplier).
 * Jenis tambahan: pengeboran, epci, pembangkit, regulasi, sistem, csr (lihat MedcoDemoSeeder).
 */

return [
    'organization' => [
        'name' => 'PT Medco Energi Internasional Tbk',
        'short_name' => 'MedcoEnergi',
        'code' => 'MEDC',
        'sector' => 'swasta',
        'email_domain' => 'demo.medco.test',
        'make_current' => false,
    ],

    // 1 juta AS$ = Rp16,5 miliar.
    'fiscal_year' => ['year' => 2026, 'ceiling' => 23_100_000_000_000, 'multiplier' => 16_500_000_000],

    'description' => 'Proyek %s tahun %d. Data demo: anggaran, jadwal, dan kemajuan adalah karangan.',
    'reference_prefix' => 'PO',

    // Proyek yang menurut laporan berjalan sesuai jadwal; kemajuannya tidak diacak.
    'on_track' => ['Bualuang Fase I, Tailan'],

    // [kode, sasaran strategis, warna, [[KPI, satuan, target 2026, realisasi 2025, skor 2025]]]
    'goals' => [
        ['SS1', 'Produksi migas 165–170 mboepd dengan biaya rendah', '#0EA5E9', [
            ['Produksi migas bersih', 'mboepd', '165–170', '156,1', 100],
            ['Biaya kas produksi', 'AS$/boe', '< 10,0', '8,6', 114],
            ['Tambahan cadangan 2P (Oman KSF dan Blok 60)', 'mmbo', '10', '9', 100],
        ]],
        ['SS2', 'Penjualan listrik ±4.550 GWh dengan porsi EBT yang meningkat', '#10B981', [
            ['Penjualan ketenagalistrikan', 'GWh', '4.550', '4.371', 102],
            ['Porsi penjualan energi terbarukan', '%', '27%', '25%', 100],
            ['Kapasitas IPP terpasang', 'MW', '1.011', '1.011', 100],
        ]],
        ['SS3', 'Proyek pertumbuhan selesai tepat waktu dan sesuai anggaran FID', '#D4A72C', [
            ['Bualuang Fase I mulai berproduksi', 'triwulan', 'Q2-2026', 'Terubuk lebih cepat dari jadwal FID', 110],
            ['Senoro Fase 2A beroperasi penuh', 'triwulan', 'Q2-2026', 'Pengeboran South Senoro selesai', 100],
            ['Kemajuan pengembangan Sakakemang (onstream Q3-2027)', '%', '45%', '–', null],
            ['Realisasi biaya proyek terhadap anggaran FID', '%', '≤ 100%', '91% (Terubuk)', 109],
        ]],
        ['SS4', 'Operasi selamat dan andal', '#F97316', [
            ['TRIR migas', 'per 200.000 jam', '≤ 0,30', '0,15', 120],
            ['Insiden Process Safety Event Tier 1', 'kejadian', '0', '0', 100],
            ['Efisiensi operasi langsung Corridor', '%', '98%', '98,8%', 101],
        ]],
        ['SS5', 'Penurunan emisi sesuai peta jalan keberlanjutan', '#14B8A6', [
            ['Penurunan emisi GRK cakupan 1 dan 2 (dari 2019)', '%', '32%', '30%', 120],
            ['Penurunan emisi metana migas (dari 2019)', '%', '42%', '40%', 120],
            ['Inisiatif penurunan emisi terlaksana', 'inisiatif', '45', '44', 100],
        ]],
        ['SS6', 'Struktur modal sehat dan tata kelola terdigitalisasi', '#A78BFA', [
            ['Belanja modal migas', 'AS$ juta', '≤ 450', '402', 100],
            ['Kemajuan migrasi SAP S/4HANA (selesai 2027)', '%', '70%', '25%', 100],
            ['Perpanjangan PSC yang disetujui', 'PSC', '3', '1', 100],
        ]],
        ['SS7', 'Manfaat nyata bagi masyarakat di wilayah operasi', '#EC4899', [
            ['Penerima manfaat program pengembangan masyarakat', 'jiwa', '25.000', '21.800', 100],
            ['Desa binaan program kesehatan dan gizi', 'desa', '20', '18', 100],
        ]],
    ],

    // Capaian KPI s.d. triwulan III 2026 (karangan): [realisasi, skor]
    'indicator_progress' => [
        'SS1' => [['166,4', 100], ['9,1', 109], ['6,2', 62]],
        'SS2' => [['3.398 (s.d. Sep)', 99], ['26,3%', 97], ['1.036', 102]],
        'SS3' => [['Juni 2026', 100], ['Juli 2026', 85], ['39%', 87], ['104%', 96]],
        'SS4' => [['0,21', 120], ['0', 100], ['98,4%', 100]],
        'SS5' => [['31%', 97], ['43%', 102], ['37', 82]],
        'SS6' => [['318', 100], ['58%', 83], ['1', 33]],
        'SS7' => [['19.600', 78], ['17', 85]],
    ],

    // unit => [nama unit, [[proyek, jenis, sasaran, AS$ juta, sumber]]]
    'units' => [
        'Corridor' => ['PSC Corridor, Sumatra Selatan', [
            ['Pengembangan Lapangan Sambar (3 sumur)', 'pengeboran', 'SS3', 38, 'Capex'],
            ['Pipa gas Sambar–Dayung 25 km', 'epci', 'SS3', 22, 'Capex'],
            ['Pengeboran sumur tambahan Suban', 'pengeboran', 'SS1', 31, 'Capex'],
            ['Pengeboran infill Corridor (3 sumur)', 'pengeboran', 'SS1', 24, 'Capex'],
            ['Interpretasi seismik 3D Rebonjaro dan Sumpal', 'kajian', 'SS1', 4.5, 'Capex'],
            ['Optimasi Waste Heat Boiler Grissik', 'fisik', 'SS5', 3.2, 'Capex'],
            ['PLTS atap fasilitas Corridor tahap 2', 'fisik', 'SS5', 1.4, 'Capex'],
            ['Turnaround fasilitas Grissik', 'fisik', 'SS4', 9, 'Opex'],
            ['Operasi dan pemeliharaan fasilitas Corridor', 'rutin', 'SS1', 85, 'Opex'],
        ]],
        'Natuna' => ['PSC South Natuna Sea Block B, Kepulauan Riau', [
            ['Pengeboran sumur pengembangan West Belut H-04', 'pengeboran', 'SS1', 18, 'Capex'],
            ['Optimasi produksi Forel–Bronang dan FPSO Marlin Natuna', 'fisik', 'SS1', 6, 'Capex'],
            ['Pengurangan gas suar Lift Gas Plant Belida', 'fisik', 'SS5', 2.8, 'Capex'],
            ['Perpanjangan PSC South Natuna Sea Block B (2028–2048)', 'regulasi', 'SS6', 1.2, 'Opex'],
            ['Operasi 18 platform, FSO, dan dua FPSO', 'rutin', 'SS1', 70, 'Opex'],
            ['Program Pelestarian Seni dan Budaya Melayu Anambas', 'csr', 'SS7', 0.15, 'Opex'],
        ]],
        'Sumsel' => ['PSC South Sumatra, Rimau, Lematang, dan Amanah', [
            ['Produksi awal penemuan West Kalabau (Rimau)', 'fisik', 'SS1', 7.5, 'Capex'],
            ['Optimasi waterflood lapangan Kaji Semoga', 'fisik', 'SS1', 5, 'Capex'],
            ['Perpanjangan PSC Lematang', 'regulasi', 'SS6', 0.6, 'Opex'],
            ['Persiapan survei seismik PSC Amanah', 'kajian', 'SS1', 2.2, 'Capex'],
            ['Amendemen PJBG dengan PGN dan pembeli baru', 'regulasi', 'SS1', 0.3, 'Opex'],
        ]],
        'Sakakemang' => ['PSC Sakakemang, Sumatra Selatan', [
            ['EPCI fasilitas produksi Sakakemang', 'epci', 'SS3', 95, 'Capex'],
            ['Pengeboran sumur pengembangan Sakakemang', 'pengeboran', 'SS3', 42, 'Capex'],
            ['AMDAL dan perizinan pengembangan Sakakemang', 'regulasi', 'SS3', 1.1, 'Capex'],
        ]],
        'Aceh' => ['PSC Blok A, Aceh', [
            ['Pengembangan Blok A Fase 2', 'epci', 'SS1', 28, 'Capex'],
            ['Acid fracturing dan penggantian wellhead', 'fisik', 'SS1', 4, 'Opex'],
            ['Proposal perpanjangan PSC Blok A 20 tahun', 'regulasi', 'SS6', 0.5, 'Opex'],
            ['Fasilitas air bersih pascabanjir Aceh Timur', 'csr', 'SS7', 0.35, 'Opex'],
        ]],
        'Jatim' => ['PSC Madura Offshore dan Sampang, Jawa Timur', [
            ['Perpanjangan PSC Madura Offshore dan Sampang', 'regulasi', 'SS6', 0.8, 'Opex'],
            ['Intervensi sumur Maleo dan Peluang', 'fisik', 'SS1', 3.5, 'Opex'],
            ['Optimasi bahan bakar transportasi laut', 'kajian', 'SS5', 0.4, 'Opex'],
        ]],
        'Kalimantan' => ['PSC Tarakan, Bangkanai, dan Simenggaris', [
            ['Pengeboran dua sumur Tarakan', 'pengeboran', 'SS1', 14, 'Capex'],
            ['Rencana Pengembangan Kerendan Tahap II', 'kajian', 'SS1', 3, 'Capex'],
            ['Implementasi PJBG baru Tarakan', 'regulasi', 'SS1', 0.2, 'Opex'],
            ['Divestasi PSC Simenggaris', 'regulasi', 'SS6', 0.5, 'Opex'],
        ]],
        'Internasional' => ['Aset internasional: Oman, Tailan, Malaysia', [
            ['Pengeboran pengembangan Blok 60 Oman (Bisat dan ABB)', 'pengeboran', 'SS1', 60, 'Capex'],
            ['Proyek Bisat-E: kapasitas pengolahan air', 'epci', 'SS3', 18, 'Capex'],
            ['Bualuang Fase I, Tailan', 'epci', 'SS3', 45, 'Capex'],
            ['Kampanye workover dan infill Bualuang', 'pengeboran', 'SS1', 12, 'Capex'],
            ['Uji coba Chemical EOR Simsim (Karim Small Fields)', 'kajian', 'SS1', 5, 'Capex'],
            ['Rencana Pengembangan Steam Flood Ilham', 'kajian', 'SS3', 3, 'Capex'],
            ['Integrasi PSC Cendramas, Malaysia', 'sistem', 'SS1', 2.5, 'Opex'],
            ['Edukasi konservasi mangrove Pak Nam Chumphon', 'csr', 'SS7', 0.02, 'Opex'],
        ]],
        'Medco Power' => ['Ketenagalistrikan (Medco Power)', [
            ['PLTP Ijen Unit 2 dan 3 (75 MW)', 'pembangkit', 'SS2', 120, 'Capex'],
            ['PLTS Bali Barat 25 MWp', 'pembangkit', 'SS2', 24, 'Capex'],
            ['Ekspansi PLTGU Batam', 'pembangkit', 'SS2', 40, 'Capex'],
            ['Pengeboran eksplorasi geotermal Bonjol', 'pengeboran', 'SS2', 9, 'Capex'],
            ['Eksplorasi geotermal Samosir', 'kajian', 'SS2', 3.5, 'Capex'],
            ['Studi kelayakan PLTB Sumbawa Barat', 'kajian', 'SS2', 1.2, 'Opex'],
            ['Inisiatif Transformasi Medco Power', 'kajian', 'SS6', 2, 'Opex'],
            ['O&M PLTU Timor-1 dan Sulut-1', 'rutin', 'SS2', 14, 'Opex'],
        ]],
        'Amman Mineral' => ['Pertambangan tembaga dan emas (afiliasi, pemantauan)', [
            ['Ekspansi pabrik konsentrator Batu Hijau', 'epci', 'SS3', 180, 'Capex'],
            ['Commissioning PLTGU 450 MW dan terminal LNG Benete', 'pembangkit', 'SS3', 60, 'Capex'],
            ['Front-end engineering rinci Proyek Elang', 'kajian', 'SS3', 35, 'Capex'],
        ]],
        'Korporat' => ['Fungsi korporat', [
            ['Migrasi ERP ke SAP S/4HANA', 'sistem', 'SS6', 9, 'Capex'],
            ['Refinancing dan pembelian kembali obligasi', 'kajian', 'SS6', 1.5, 'Opex'],
            ['Penilaian keunggulan operasional (PDCA) lima aset', 'kajian', 'SS4', 0.8, 'Opex'],
            ['Pelatihan keselamatan proses dan Life Saving Rules', 'acara', 'SS4', 1.2, 'Opex'],
            ['Kajian hub CCS regional', 'kajian', 'SS5', 2.5, 'Opex'],
            ['Pelaporan metana OGMP 2.0', 'kajian', 'SS5', 0.6, 'Opex'],
            ['RUPS Tahunan 2026', 'acara', 'SS6', 0.3, 'Opex'],
            ['Laporan Tahunan dan Laporan Keberlanjutan 2026', 'kajian', 'SS6', 0.4, 'Opex'],
        ]],
        'PPM' => ['Pengembangan masyarakat dan Medco Foundation', [
            ['Posyandu dan pos gizi 14 desa Musi Banyuasin', 'csr', 'SS7', 0.25, 'Opex'],
            ['Budidaya jangkrik KUB Temu Kasih, Pekanbaru', 'csr', 'SS7', 0.03, 'Opex'],
            ['Peralatan kesehatan Posyandu, Riau', 'csr', 'SS7', 0.02, 'Opex'],
            ['Program listrik gratis Cianjur', 'csr', 'SS7', 0.05, 'Opex'],
            ['Beasiswa teknik perminyakan dan energi terbarukan', 'csr', 'SS7', 0.4, 'Opex'],
        ]],
    ],
];
