import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:http/http.dart' as http;
import 'theme.dart';
import 'header.dart';
import 'detail.dart';

class RiwayatPage extends StatefulWidget {
  const RiwayatPage({super.key});

  @override
  State<RiwayatPage> createState() => _RiwayatPageState();
}

class _RiwayatPageState extends State<RiwayatPage> {
  List<dynamic> _list = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _ambilRiwayat();
  }

  // 1. Ambil Semua Daftar Riwayat
  Future<void> _ambilRiwayat() async {
    setState(() => _loading = true);
    try {
      final idUser = UserSession.id;
      String url = '${AppTheme.apiUrl}/riwayat';
      if (idUser != null) {
        url += '?id_pengguna=$idUser';
      } else if (UserSession.name.isNotEmpty &&
          UserSession.name != 'Pengguna') {
        url += '?nama_pelapor=${Uri.encodeComponent(UserSession.name)}';
      }
      final res = await http.get(
        Uri.parse(url),
        headers: UserSession.authHeaders(),
      );
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        if (data['status'] == 'success') {
          setState(() {
            _list = data['data'] ?? [];
            _loading = false;
          });
          return;
        }
      }
    } catch (_) {}
    setState(() => _loading = false);
  }

  // 2. Hapus Laporan
  Future<void> _hapus(dynamic id) async {
    final konfirmasi = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text(
          'Hapus Laporan',
          style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.bold),
        ),
        content: Text(
          'Apakah kamu yakin ingin menghapus laporan ini?',
          style: GoogleFonts.plusJakartaSans(fontSize: 13),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Batal', style: TextStyle(color: Colors.grey)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8)),
            ),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Hapus', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );

    if (konfirmasi == true) {
      await http.delete(
        Uri.parse('${AppTheme.apiUrl}/laporan/$id'),
        headers: UserSession.authHeaders(),
      );
      _ambilRiwayat();
    }
  }

  // 3. Dialog Form Edit Laporan
  Future<void> _tampilkanDialogEdit(Map<String, dynamic> item) async {
    final idLaporan = item['id_laporan'] ?? item['id'];
    final kategoriController =
        TextEditingController(text: item['kategori'] ?? '');
    final lokasiController = TextEditingController(
      text: item['lokasi_kejadian'] ?? item['lokasi'] ?? '',
    );
    final kronologiController =
        TextEditingController(text: item['kronologi'] ?? '');

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text(
          'Edit Laporan',
          style: GoogleFonts.plusJakartaSans(
            fontWeight: FontWeight.bold,
            color: AppTheme.primaryPink,
          ),
        ),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              TextField(
                controller: kategoriController,
                style: GoogleFonts.plusJakartaSans(fontSize: 13),
                decoration: const InputDecoration(
                  labelText: 'Kategori Kejadian',
                  border: OutlineInputBorder(),
                  contentPadding:
                      EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: lokasiController,
                style: GoogleFonts.plusJakartaSans(fontSize: 13),
                decoration: const InputDecoration(
                  labelText: 'Lokasi Kejadian',
                  border: OutlineInputBorder(),
                  contentPadding:
                      EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: kronologiController,
                maxLines: 3,
                style: GoogleFonts.plusJakartaSans(fontSize: 13),
                decoration: const InputDecoration(
                  labelText: 'Kronologi Singkat',
                  border: OutlineInputBorder(),
                  contentPadding: EdgeInsets.all(12),
                ),
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Batal', style: TextStyle(color: Colors.grey)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.primaryPink,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8)),
            ),
            onPressed: () async {
              Navigator.pop(ctx);
              setState(() => _loading = true);

              try {
                final res = await http.put(
                  Uri.parse('${AppTheme.apiUrl}/laporan/$idLaporan'),
                  headers: UserSession.authHeaders(json: true),
                  body: jsonEncode({
                    'kategori': kategoriController.text,
                    'lokasi': lokasiController.text,
                    'kronologi': kronologiController.text,
                  }),
                );

                if (res.statusCode == 200 && mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('Laporan berhasil diperbarui!'),
                      backgroundColor: Colors.green,
                    ),
                  );
                }
              } catch (_) {}

              _ambilRiwayat();
            },
            child: const Text('Simpan Perubahan',
                style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return BaseLayout(
      showBackButton: false,
      child: _loading
          ? const Center(
              child: CircularProgressIndicator(color: AppTheme.primaryPink))
          : _list.isEmpty
              ? Center(
                  child: Text(
                    'Belum ada riwayat laporan.',
                    style:
                        GoogleFonts.plusJakartaSans(color: AppTheme.textGrey),
                  ),
                )
              : ListView.builder(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                  itemCount: _list.length,
                  itemBuilder: (ctx, i) {
                    final item = _list[i];
                    final status =
                        item['status_penanganan'] ?? 'Menunggu Verifikasi';
                    Color bg = AppTheme.badgeYellowBg;
                    Color txt = AppTheme.badgeYellowText;
                    if (status == 'Diproses' || status == 'Sedang Diproses') {
                      bg = AppTheme.badgeBlueBg;
                      txt = AppTheme.badgeBlueText;
                    } else if (status == 'Selesai') {
                      bg = AppTheme.badgeGreenBg;
                      txt = AppTheme.badgeGreenText;
                    }

                    // Format tanggal bersih tanpa T00:00:00.000000Z
                    String rawTanggal =
                        (item['tanggal_kejadian'] ?? '').toString();
                    String cleanTanggal = rawTanggal.contains('T')
                        ? rawTanggal.split('T').first
                        : rawTanggal;

                    final idLaporan =
                        item['id_laporan'] ?? item['id'] ?? (i + 1);

                    return Container(
                      margin: const EdgeInsets.only(bottom: 12),
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: const Color(0xFFF0F0F2)),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.02),
                            blurRadius: 6,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: Row(
                        children: [
                          // Bagian Informasi Laporan (Klik untuk Detail)
                          Expanded(
                            child: InkWell(
                              onTap: () => Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => DetailPage(laporan: item),
                                ),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    item['nomor_laporan'] ?? 'LAP-$idLaporan',
                                    style: GoogleFonts.plusJakartaSans(
                                      color: AppTheme.primaryPink,
                                      fontWeight: FontWeight.bold,
                                      fontSize: 13,
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    item['kategori'] ?? '',
                                    style: GoogleFonts.plusJakartaSans(
                                      fontWeight: FontWeight.w600,
                                      fontSize: 12,
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    cleanTanggal,
                                    style: GoogleFonts.plusJakartaSans(
                                      fontSize: 10,
                                      color: AppTheme.textGrey,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),

                          // Badge Status
                          Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: bg,
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              status,
                              style: GoogleFonts.plusJakartaSans(
                                color: txt,
                                fontSize: 10,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                          const SizedBox(width: 4),

                          // Tombol Edit
                          IconButton(
                            icon: const Icon(Icons.edit_outlined,
                                color: Colors.blue, size: 20),
                            tooltip: 'Edit Laporan',
                            onPressed: () => _tampilkanDialogEdit(item),
                          ),

                          // Tombol Hapus
                          IconButton(
                            icon: const Icon(Icons.delete_outline,
                                color: Colors.red, size: 20),
                            tooltip: 'Hapus Laporan',
                            onPressed: () => _hapus(idLaporan),
                          ),
                        ],
                      ),
                    );
                  },
                ),
    );
  }
}
