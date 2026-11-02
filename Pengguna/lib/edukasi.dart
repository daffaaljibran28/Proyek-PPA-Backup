import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:http/http.dart' as http;

import 'header.dart';
import 'theme.dart';

class EdukasiPage extends StatefulWidget {
  const EdukasiPage({super.key});

  @override
  State<EdukasiPage> createState() => _EdukasiPageState();
}

class _EdukasiPageState extends State<EdukasiPage> {
  late Future<List<Map<String, dynamic>>> _informasiFuture;

  @override
  void initState() {
    super.initState();
    _informasiFuture = _loadInformasi();
  }

  Future<List<Map<String, dynamic>>> _loadInformasi() async {
    final response = await http.get(
      Uri.parse('${AppTheme.apiUrl}/informasi'),
      headers: const {'Accept': 'application/json'},
    );

    final data = jsonDecode(response.body);
    if (response.statusCode != 200 || data is! Map<String, dynamic>) {
      final message =
          data is Map<String, dynamic> ? data['message']?.toString() : null;
      throw StateError(message ?? 'Informasi gagal dimuat dari server.');
    }

    final items = data['data'];
    if (data['status'] != 'success' || items is! List) {
      throw const FormatException(
          'Format data informasi dari server tidak valid.');
    }

    return items
        .whereType<Map<String, dynamic>>()
        .map((item) => Map<String, dynamic>.from(item))
        .toList();
  }

  Future<void> _refresh() async {
    final future = _loadInformasi();
    setState(() => _informasiFuture = future);
    await future;
  }

  String? _imageUrl(dynamic imagePath) {
    if (imagePath is! String || imagePath.isEmpty) return null;

    final apiUri = Uri.parse(AppTheme.apiUrl);
    return apiUri.resolve('/storage/$imagePath').toString();
  }

  String _publishedDate(dynamic value) {
    if (value is! String) return '';

    final date = DateTime.tryParse(value);
    if (date == null) return '';

    const months = [
      'Jan',
      'Feb',
      'Mar',
      'Apr',
      'Mei',
      'Jun',
      'Jul',
      'Agu',
      'Sep',
      'Okt',
      'Nov',
      'Des',
    ];
    return '${date.day} ${months[date.month - 1]} ${date.year}';
  }

  @override
  Widget build(BuildContext context) {
    return BaseLayout(
      showBackButton: true,
      child: FutureBuilder<List<Map<String, dynamic>>>(
        future: _informasiFuture,
        builder: (context, snapshot) {
          return RefreshIndicator(
            onRefresh: _refresh,
            child: ListView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.fromLTRB(20, 18, 20, 28),
              children: [
                Text(
                  'Informasi & Edukasi',
                  style: GoogleFonts.plusJakartaSans(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: AppTheme.textDark,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  'Artikel dan panduan yang diterbitkan oleh admin.',
                  style: GoogleFonts.plusJakartaSans(
                    fontSize: 12,
                    height: 1.5,
                    color: AppTheme.textGrey,
                  ),
                ),
                const SizedBox(height: 20),
                if (snapshot.connectionState == ConnectionState.waiting)
                  const Padding(
                    padding: EdgeInsets.symmetric(vertical: 48),
                    child: Center(child: CircularProgressIndicator()),
                  )
                else if (snapshot.hasError)
                  _messageCard(
                    icon: Icons.cloud_off_outlined,
                    message: 'Informasi belum dapat dimuat.',
                    detail: snapshot.error.toString(),
                    action: TextButton.icon(
                      onPressed: () {
                        setState(() => _informasiFuture = _loadInformasi());
                      },
                      icon: const Icon(Icons.refresh),
                      label: const Text('Coba lagi'),
                    ),
                  )
                else if (snapshot.data!.isEmpty)
                  _messageCard(
                    icon: Icons.menu_book_outlined,
                    message: 'Belum ada informasi atau edukasi.',
                    detail:
                        'Informasi yang diterbitkan admin akan tampil di sini.',
                  )
                else
                  ...snapshot.data!.map(_informationCard),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _messageCard({
    required IconData icon,
    required String message,
    required String detail,
    Widget? action,
  }) =>
      Container(
        padding: const EdgeInsets.all(24),
        decoration: BoxDecoration(
          color: Colors.white,
          border: Border.all(color: const Color(0xFFF0F0F2)),
          borderRadius: BorderRadius.circular(14),
        ),
        child: Column(
          children: [
            Icon(icon, size: 32, color: AppTheme.primaryPink),
            const SizedBox(height: 10),
            Text(
              message,
              textAlign: TextAlign.center,
              style: GoogleFonts.plusJakartaSans(
                fontSize: 13,
                fontWeight: FontWeight.bold,
                color: AppTheme.textDark,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              detail,
              textAlign: TextAlign.center,
              style: GoogleFonts.plusJakartaSans(
                fontSize: 11,
                height: 1.5,
                color: AppTheme.textGrey,
              ),
            ),
            if (action != null) action,
          ],
        ),
      );

  Widget _informationCard(Map<String, dynamic> item) {
    final imageUrl = _imageUrl(item['gambar']);
    final date = _publishedDate(item['tanggal_publish']);

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      clipBehavior: Clip.antiAlias,
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border.all(color: const Color(0xFFF0F0F2)),
        borderRadius: BorderRadius.circular(14),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (imageUrl != null)
            Image.network(
              imageUrl,
              width: double.infinity,
              height: 190,
              fit: BoxFit.cover,
              errorBuilder: (context, error, stackTrace) =>
                  const SizedBox.shrink(),
            ),
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (date.isNotEmpty) ...[
                  Text(
                    date,
                    style: GoogleFonts.plusJakartaSans(
                      fontSize: 10,
                      fontWeight: FontWeight.w600,
                      color: AppTheme.primaryPink,
                    ),
                  ),
                  const SizedBox(height: 6),
                ],
                Text(
                  item['judul']?.toString() ?? '',
                  style: GoogleFonts.plusJakartaSans(
                    fontSize: 14,
                    fontWeight: FontWeight.bold,
                    color: AppTheme.textDark,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  item['konten']?.toString() ?? '',
                  style: GoogleFonts.plusJakartaSans(
                    fontSize: 12,
                    height: 1.6,
                    color: AppTheme.textGrey,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
