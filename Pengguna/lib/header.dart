import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:http/http.dart' as http;
import 'theme.dart';
import 'login.dart';

class BaseLayout extends StatelessWidget {
  final Widget child;
  final bool showBackButton;

  const BaseLayout({
    super.key,
    required this.child,
    this.showBackButton = false,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8F9FA),
      body: SafeArea(
        child: Column(
          children: [
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
              decoration: const BoxDecoration(
                color: AppTheme.primaryPink,
                borderRadius: BorderRadius.only(
                  bottomLeft: Radius.circular(24),
                  bottomRight: Radius.circular(24),
                ),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  showBackButton
                      ? Container(
                          width: 44,
                          height: 44,
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.16),
                            shape: BoxShape.circle,
                            border: Border.all(
                              color: Colors.white.withValues(alpha: 0.24),
                            ),
                          ),
                          child: IconButton(
                            tooltip: 'Kembali',
                            icon: const Icon(Icons.arrow_back_rounded,
                                color: Colors.white, size: 22),
                            onPressed: () async {
                              await Navigator.maybePop(context);
                            },
                          ),
                        )
                      : Row(
                          children: [
                            Container(
                              width: 42,
                              height: 42,
                              decoration: BoxDecoration(
                                color: Colors.white,
                                shape: BoxShape.circle,
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withValues(alpha: 0.08),
                                    blurRadius: 8,
                                    offset: const Offset(0, 3),
                                  ),
                                ],
                              ),
                              child: const Icon(
                                Icons.volunteer_activism,
                                color: AppTheme.primaryPink,
                                size: 25,
                              ),
                            ),
                            const SizedBox(width: 9),
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Text(
                                  'SAPA',
                                  style: GoogleFonts.plusJakartaSans(
                                    color: Colors.white,
                                    fontSize: 17,
                                    height: 1.1,
                                    fontWeight: FontWeight.w800,
                                  ),
                                ),
                                Text(
                                  'Sistem Pelaporan Aman',
                                  style: GoogleFonts.plusJakartaSans(
                                    color: Colors.white.withValues(alpha: 0.8),
                                    fontSize: 9,
                                    height: 1.2,
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                  Row(
                    children: [
                      const _NotificationBell(),
                      const SizedBox(width: 4),
                      PopupMenuButton<String>(
                        offset: const Offset(0, 45),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14),
                        ),
                        onSelected: (value) async {
                          if (value == 'logout') {
                            showDialog(
                              context: context,
                              builder: (context) => AlertDialog(
                                title: Text('Keluar Akun',
                                    style: GoogleFonts.plusJakartaSans(
                                        fontWeight: FontWeight.bold,
                                        fontSize: 16)),
                                content: Text(
                                    'Apakah Anda yakin ingin keluar dari aplikasi?',
                                    style: GoogleFonts.plusJakartaSans(
                                        fontSize: 13)),
                                actions: [
                                  TextButton(
                                    onPressed: () => Navigator.pop(context),
                                    child: Text('Batal',
                                        style: GoogleFonts.plusJakartaSans(
                                            color: Colors.grey)),
                                  ),
                                  TextButton(
                                    onPressed: () async {
                                      try {
                                        await UserSession.logout();
                                      } catch (error) {
                                        debugPrint('Logout gagal: $error');
                                        await UserSession.clear();
                                      }
                                      if (!context.mounted) return;
                                      Navigator.pop(context);
                                      Navigator.pushAndRemoveUntil(
                                        context,
                                        MaterialPageRoute(
                                            builder: (_) =>
                                                Login(onLoginSuccess: () {})),
                                        (route) => false,
                                      );
                                    },
                                    child: Text('Keluar',
                                        style: GoogleFonts.plusJakartaSans(
                                            fontWeight: FontWeight.bold,
                                            color: Colors.red)),
                                  ),
                                ],
                              ),
                            );
                          }
                        },
                        itemBuilder: (BuildContext context) => [
                          PopupMenuItem<String>(
                            value: 'logout',
                            child: Row(
                              children: [
                                const Icon(Icons.logout,
                                    color: Colors.red, size: 18),
                                const SizedBox(width: 10),
                                Text(
                                  'Keluar',
                                  style: GoogleFonts.plusJakartaSans(
                                    fontSize: 13,
                                    fontWeight: FontWeight.bold,
                                    color: Colors.red,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                        child: Container(
                          padding: const EdgeInsets.all(2),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            shape: BoxShape.circle,
                            border: Border.all(color: Colors.white, width: 2),
                          ),
                          child: const CircleAvatar(
                            radius: 16,
                            backgroundColor: Color(0xFFFFEEF2),
                            child: Icon(Icons.person,
                                color: AppTheme.primaryPink, size: 18),
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            Expanded(
              child: child,
            ),
          ],
        ),
      ),
    );
  }
}

class _NotificationBell extends StatefulWidget {
  const _NotificationBell();

  @override
  State<_NotificationBell> createState() => _NotificationBellState();
}

class _NotificationBellState extends State<_NotificationBell> {
  late Future<List<Map<String, dynamic>>> _notifikasiFuture;

  @override
  void initState() {
    super.initState();
    _notifikasiFuture = _ambilNotifikasi();
  }

  Future<List<Map<String, dynamic>>> _ambilNotifikasi() async {
    final idPengguna = UserSession.id;
    if (idPengguna == null) return [];

    final response = await http.get(
      Uri.parse('${AppTheme.apiUrl}/notifikasi/$idPengguna'),
      headers: UserSession.authHeaders(),
    );
    if (response.statusCode != 200) {
      throw Exception('Notifikasi gagal dimuat');
    }

    final responseData = jsonDecode(response.body) as Map<String, dynamic>;
    final data = responseData['data'] as List<dynamic>? ?? [];
    return data.map((item) => Map<String, dynamic>.from(item as Map)).toList();
  }

  String _pesanNotifikasi(Map<String, dynamic> item) {
    for (final key in ['pesan_pelapor', 'pesan', 'deskripsi']) {
      final value = item[key];
      if (value is String &&
          value.trim().isNotEmpty &&
          value.trim().toLowerCase() != 'null') {
        return value.trim();
      }
    }

    switch (item['status_penanganan']) {
      case 'Menunggu Verifikasi':
        return 'Laporan Anda telah diterima dan menunggu verifikasi.';
      case 'Sedang Diproses':
      case 'Diproses':
        return 'Laporan Anda sedang ditindaklanjuti oleh petugas.';
      case 'Selesai':
        return 'Laporan Anda telah selesai ditangani.';
      case 'Ditolak':
        return 'Laporan Anda ditolak. Silakan periksa detail laporan untuk informasi lebih lanjut.';
      default:
        return 'Status laporan Anda telah diperbarui.';
    }
  }

  Future<void> _hapusNotifikasi(
      int idLaporan, StateSetter setModalState) async {
    final idPengguna = UserSession.id;
    if (idPengguna == null) return;

    final konfirmasi = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: Text(
          'Hapus notifikasi?',
          style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.bold),
        ),
        content: Text(
          'Notifikasi akan disembunyikan. Laporan tetap tersimpan.',
          style: GoogleFonts.plusJakartaSans(fontSize: 13),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogContext, false),
            child: Text(
              'Jangan hapus',
              style: GoogleFonts.plusJakartaSans(color: AppTheme.textGrey),
            ),
          ),
          TextButton(
            onPressed: () => Navigator.pop(dialogContext, true),
            child: Text(
              'Hapus',
              style: GoogleFonts.plusJakartaSans(
                color: AppTheme.primaryPink,
                fontWeight: FontWeight.bold,
              ),
            ),
          ),
        ],
      ),
    );
    if (konfirmasi != true || !mounted) return;

    try {
      final response = await http.delete(
        Uri.parse('${AppTheme.apiUrl}/notifikasi/$idPengguna/$idLaporan'),
        headers: UserSession.authHeaders(),
      );
      if (!mounted) return;
      if (response.statusCode != 200) {
        throw Exception('Notifikasi gagal dihapus');
      }

      setState(() {
        _notifikasiFuture = _ambilNotifikasi();
      });
      setModalState(() {});
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'Notifikasi berhasil dihapus.',
            style: GoogleFonts.plusJakartaSans(color: Colors.white),
          ),
        ),
      );
    } catch (_) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'Notifikasi gagal dihapus. Silakan coba lagi.',
            style: GoogleFonts.plusJakartaSans(color: Colors.white),
          ),
        ),
      );
    }
  }

  Future<void> _hapusLaporan(int idLaporan, StateSetter setModalState) async {
    final konfirmasi = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: Text(
          'Hapus laporan?',
          style: GoogleFonts.plusJakartaSans(fontWeight: FontWeight.bold),
        ),
        content: Text(
          'Laporan yang masih menunggu verifikasi akan dihapus permanen beserta lampirannya. Tindakan ini tidak dapat dibatalkan.',
          style: GoogleFonts.plusJakartaSans(fontSize: 13),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogContext, false),
            child: Text(
              'Jangan hapus',
              style: GoogleFonts.plusJakartaSans(color: AppTheme.textGrey),
            ),
          ),
          TextButton(
            onPressed: () => Navigator.pop(dialogContext, true),
            child: Text(
              'Hapus laporan',
              style: GoogleFonts.plusJakartaSans(
                color: Colors.red,
                fontWeight: FontWeight.bold,
              ),
            ),
          ),
        ],
      ),
    );
    if (konfirmasi != true || !mounted) return;

    try {
      final response = await http.delete(
        Uri.parse('${AppTheme.apiUrl}/laporan/$idLaporan'),
        headers: UserSession.authHeaders(),
      );
      if (!mounted) return;
      if (response.statusCode != 200) {
        final data = jsonDecode(response.body);
        final message =
            data is Map<String, dynamic> ? data['message']?.toString() : null;
        throw StateError(message ?? 'Laporan gagal dihapus.');
      }

      setState(() {
        _notifikasiFuture = _ambilNotifikasi();
      });
      setModalState(() {});
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'Laporan berhasil dihapus.',
            style: GoogleFonts.plusJakartaSans(color: Colors.white),
          ),
        ),
      );
    } catch (error) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            error is StateError
                ? error.message
                : 'Laporan gagal dihapus. Silakan coba lagi.',
            style: GoogleFonts.plusJakartaSans(color: Colors.white),
          ),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  Future<void> _bukaNotifikasi() async {
    setState(() {
      _notifikasiFuture = _ambilNotifikasi();
    });
    await showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (sheetContext) => StatefulBuilder(
        builder: (context, setModalState) => Container(
          height: MediaQuery.of(sheetContext).size.height * 0.68,
          padding: const EdgeInsets.fromLTRB(20, 16, 20, 12),
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  const Icon(Icons.notifications_active_outlined,
                      color: AppTheme.primaryPink),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      'Pembaruan Laporan',
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  IconButton(
                    onPressed: () => Navigator.pop(sheetContext),
                    icon: const Icon(Icons.close),
                    tooltip: 'Tutup',
                  ),
                ],
              ),
              const Divider(height: 1),
              const SizedBox(height: 12),
              Expanded(
                child: FutureBuilder<List<Map<String, dynamic>>>(
                  future: _notifikasiFuture,
                  builder: (context, snapshot) {
                    if (snapshot.connectionState == ConnectionState.waiting) {
                      return const Center(
                        child: CircularProgressIndicator(
                          color: AppTheme.primaryPink,
                        ),
                      );
                    }
                    if (snapshot.hasError) {
                      return Center(
                        child: Text(
                          'Notifikasi belum dapat dimuat. Periksa koneksi Anda.',
                          textAlign: TextAlign.center,
                          style: GoogleFonts.plusJakartaSans(
                            fontSize: 13,
                            color: AppTheme.textGrey,
                          ),
                        ),
                      );
                    }

                    final items = snapshot.data ?? [];
                    if (items.isEmpty) {
                      return Center(
                        child: Text(
                          'Belum ada catatan atau pembaruan dari admin.',
                          textAlign: TextAlign.center,
                          style: GoogleFonts.plusJakartaSans(
                            fontSize: 13,
                            color: AppTheme.textGrey,
                          ),
                        ),
                      );
                    }

                    return ListView.separated(
                      itemCount: items.length,
                      separatorBuilder: (_, __) => const SizedBox(height: 10),
                      itemBuilder: (context, index) {
                        final item = items[index];
                        final updatedAt = (item['updated_at'] ?? '').toString();
                        final waktu = updatedAt.isEmpty
                            ? ''
                            : updatedAt.replaceFirst('T', ' ').split('.').first;
                        final idLaporan =
                            int.tryParse(item['id_laporan'].toString());
                        return Container(
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            color: const Color(0xFFFFF7F9),
                            border: Border.all(color: AppTheme.borderPink),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Expanded(
                                    child: Text(
                                      (item['nomor_laporan'] ??
                                              'Pembaruan laporan')
                                          .toString(),
                                      style: GoogleFonts.plusJakartaSans(
                                        fontSize: 12,
                                        fontWeight: FontWeight.bold,
                                        color: AppTheme.primaryPink,
                                      ),
                                    ),
                                  ),
                                  IconButton(
                                    tooltip: 'Hapus notifikasi',
                                    visualDensity: VisualDensity.compact,
                                    onPressed: idLaporan == null
                                        ? null
                                        : () => _hapusNotifikasi(
                                              idLaporan,
                                              setModalState,
                                            ),
                                    icon: const Icon(
                                      Icons.delete_outline,
                                      color: AppTheme.textGrey,
                                      size: 20,
                                    ),
                                  ),
                                  if (item['dapat_dihapus'] == true)
                                    IconButton(
                                      tooltip:
                                          'Hapus laporan yang belum diproses',
                                      visualDensity: VisualDensity.compact,
                                      onPressed: idLaporan == null
                                          ? null
                                          : () => _hapusLaporan(
                                                idLaporan,
                                                setModalState,
                                              ),
                                      icon: const Icon(
                                        Icons.delete_forever_outlined,
                                        color: Colors.red,
                                        size: 20,
                                      ),
                                    ),
                                ],
                              ),
                              const SizedBox(height: 4),
                              Text(
                                'Status: ${item['status_penanganan'] ?? '-'}',
                                style: GoogleFonts.plusJakartaSans(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w600,
                                  color: AppTheme.textDark,
                                ),
                              ),
                              const SizedBox(height: 8),
                              Text(
                                _pesanNotifikasi(item),
                                style: GoogleFonts.plusJakartaSans(
                                  fontSize: 12,
                                  color: AppTheme.textDark,
                                  height: 1.4,
                                ),
                              ),
                              if (waktu.isNotEmpty) ...[
                                const SizedBox(height: 8),
                                Text(
                                  waktu,
                                  style: GoogleFonts.plusJakartaSans(
                                    fontSize: 10,
                                    color: AppTheme.textGrey,
                                  ),
                                ),
                              ],
                            ],
                          ),
                        );
                      },
                    );
                  },
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<List<Map<String, dynamic>>>(
      future: _notifikasiFuture,
      builder: (context, snapshot) {
        final jumlah = snapshot.data?.length ?? 0;
        return IconButton(
          tooltip: 'Pembaruan laporan',
          onPressed: _bukaNotifikasi,
          icon: Stack(
            clipBehavior: Clip.none,
            children: [
              const Icon(Icons.notifications_none, color: Colors.white),
              if (jumlah > 0)
                Positioned(
                  top: -5,
                  right: -7,
                  child: Container(
                    constraints:
                        const BoxConstraints(minWidth: 16, minHeight: 16),
                    padding: const EdgeInsets.symmetric(horizontal: 4),
                    decoration: const BoxDecoration(
                      color: Color(0xFFB91C4B),
                      shape: BoxShape.circle,
                    ),
                    alignment: Alignment.center,
                    child: Text(
                      jumlah > 9 ? '9+' : '$jumlah',
                      style: GoogleFonts.plusJakartaSans(
                        color: Colors.white,
                        fontSize: 9,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ),
            ],
          ),
        );
      },
    );
  }
}
