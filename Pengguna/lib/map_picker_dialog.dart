import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:http/http.dart' as http;
import 'theme.dart';

class MapPickerDialog extends StatefulWidget {
  final String initialLocation;
  const MapPickerDialog({super.key, this.initialLocation = ''});

  @override
  State<MapPickerDialog> createState() => _MapPickerDialogState();
}

class _MapPickerDialogState extends State<MapPickerDialog> {
  final TextEditingController _searchCtrl = TextEditingController();
  List<dynamic> _searchResults = [];
  bool _isSearching = false;
  bool _isGeocoding = false;

  // Lat & Lng default (Jakarta / Indonesia)
  double _lat = -6.2088;
  double _lng = 106.8456;
  String _selectedAddress = '';

  @override
  void initState() {
    super.initState();
    if (widget.initialLocation.isNotEmpty) {
      _searchCtrl.text = widget.initialLocation;
      _selectedAddress = widget.initialLocation;
      _cariAlamat(widget.initialLocation);
    } else {
      _reverseGeocode(_lat, _lng);
    }
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  // Reverse Geocoding: Mengubah koordinat (lat, lng) menjadi Alamat Teks
  Future<void> _reverseGeocode(double lat, double lng) async {
    setState(() => _isGeocoding = true);
    try {
      final url = Uri.parse(
          'https://nominatim.openstreetmap.org/reverse?format=json&lat=$lat&lon=$lng&zoom=18&addressdetails=1');
      final res = await http.get(url, headers: {'User-Agent': 'SahabatPPA_FlutterApp/1.0'});
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        if (data != null && data['display_name'] != null) {
          setState(() {
            _selectedAddress = data['display_name'];
            _searchCtrl.text = _selectedAddress;
          });
        }
      }
    } catch (e) {
      debugPrint('Error Reverse Geocode: $e');
    } finally {
      if (mounted) setState(() => _isGeocoding = false);
    }
  }

  // Forward Geocoding: Cari Alamat dari Input Teks
  Future<void> _cariAlamat(String query) async {
    if (query.trim().isEmpty) return;
    setState(() => _isSearching = true);
    try {
      final url = Uri.parse(
          'https://nominatim.openstreetmap.org/search?format=json&q=${Uri.encodeComponent(query)}&limit=5&countrycodes=id');
      final res = await http.get(url, headers: {'User-Agent': 'SahabatPPA_FlutterApp/1.0'});
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        setState(() {
          _searchResults = data ?? [];
        });
      }
    } catch (e) {
      debugPrint('Error Search Alamat: $e');
    } finally {
      if (mounted) setState(() => _isSearching = false);
    }
  }

  void _pilihHasilPencarian(dynamic item) {
    final double lat = double.tryParse(item['lat']?.toString() ?? '') ?? _lat;
    final double lon = double.tryParse(item['lon']?.toString() ?? '') ?? _lng;
    final String displayName = item['display_name'] ?? '';

    setState(() {
      _lat = lat;
      _lng = lon;
      _selectedAddress = displayName;
      _searchCtrl.text = displayName;
      _searchResults = [];
    });
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      child: Container(
        width: MediaQuery.of(context).size.width > 600 ? 550 : double.infinity,
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header Dialog
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    const Icon(Icons.map_outlined, color: AppTheme.primaryPink, size: 22),
                    const SizedBox(width: 8),
                    Text(
                      'Pilih Lokasi Kejadian',
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                        color: AppTheme.primaryPink,
                      ),
                    ),
                  ],
                ),
                IconButton(
                  icon: const Icon(Icons.close, color: Colors.grey, size: 20),
                  onPressed: () => Navigator.pop(context),
                ),
              ],
            ),
            const SizedBox(height: 12),

            // Search Bar Input
            TextField(
              controller: _searchCtrl,
              onSubmitted: (val) => _cariAlamat(val),
              style: GoogleFonts.plusJakartaSans(fontSize: 12),
              decoration: InputDecoration(
                hintText: 'Cari alamat, nama jalan, atau kota...',
                hintStyle: GoogleFonts.plusJakartaSans(fontSize: 11, color: Colors.grey.shade400),
                prefixIcon: const Icon(Icons.search, color: AppTheme.primaryPink, size: 18),
                suffixIcon: _isSearching
                    ? const Padding(
                        padding: EdgeInsets.all(12),
                        child: SizedBox(
                          width: 14,
                          height: 14,
                          child: CircularProgressIndicator(strokeWidth: 2, color: AppTheme.primaryPink),
                        ),
                      )
                    : IconButton(
                        icon: const Icon(Icons.arrow_forward, color: AppTheme.primaryPink, size: 18),
                        onPressed: () => _cariAlamat(_searchCtrl.text),
                      ),
                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(color: AppTheme.borderPink),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(color: AppTheme.primaryPink, width: 1.5),
                ),
              ),
            ),

            // Daftar Hasil Pencarian (jika ada)
            if (_searchResults.isNotEmpty) ...[
              const SizedBox(height: 8),
              Container(
                constraints: const BoxConstraints(maxHeight: 150),
                decoration: BoxDecoration(
                  color: Colors.grey.shade50,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: Colors.grey.shade200),
                ),
                child: ListView.separated(
                  shrinkWrap: true,
                  itemCount: _searchResults.length,
                  separatorBuilder: (_, __) => const Divider(height: 1),
                  itemBuilder: (context, index) {
                    final item = _searchResults[index];
                    return ListTile(
                      dense: true,
                      leading: const Icon(Icons.location_on, color: AppTheme.primaryPink, size: 16),
                      title: Text(
                        item['display_name'] ?? '',
                        style: GoogleFonts.plusJakartaSans(fontSize: 11),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      onTap: () => _pilihHasilPencarian(item),
                    );
                  },
                ),
              ),
            ],

            const SizedBox(height: 14),

            // Visual Card Preview Peta & Koordinat Pin
            Container(
              height: 180,
              width: double.infinity,
              decoration: BoxDecoration(
                color: Colors.pink.shade50.withOpacity(0.5),
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: AppTheme.borderPink),
              ),
              child: Stack(
                children: [
                  // Latar Belakang Peta Ilustrasi
                  Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Container(
                          width: 50,
                          height: 50,
                          decoration: BoxDecoration(
                            color: Colors.white,
                            shape: BoxShape.circle,
                            boxShadow: [
                              BoxShadow(
                                color: AppTheme.primaryPink.withOpacity(0.3),
                                blurRadius: 12,
                                spreadRadius: 2,
                              ),
                            ],
                          ),
                          child: const Icon(Icons.location_on, color: AppTheme.primaryPink, size: 30),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          'Titik Lokasi Terpilih',
                          style: GoogleFonts.plusJakartaSans(
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                            color: AppTheme.primaryPink,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'Lat: ${_lat.toStringAsFixed(4)}, Lng: ${_lng.toStringAsFixed(4)}',
                          style: GoogleFonts.plusJakartaSans(
                            fontSize: 10,
                            color: Colors.grey.shade600,
                          ),
                        ),
                      ],
                    ),
                  ),

                  // Badge Pilihan Kota Populer (Quick Pick)
                  Positioned(
                    bottom: 8,
                    left: 8,
                    right: 8,
                    child: SingleChildScrollView(
                      scrollDirection: Axis.horizontal,
                      child: Row(
                        children: [
                          _buildQuickPickChip('Jakarta Pusat'),
                          _buildQuickPickChip('Bandung'),
                          _buildQuickPickChip('Surabaya'),
                          _buildQuickPickChip('Medan'),
                          _buildQuickPickChip('Makassar'),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 12),

            // Alamat Terpilih Preview Teks
            if (_isGeocoding)
              Row(
                children: [
                  const SizedBox(
                    width: 14,
                    height: 14,
                    child: CircularProgressIndicator(strokeWidth: 2, color: AppTheme.primaryPink),
                  ),
                  const SizedBox(width: 8),
                  Text(
                    'Mengambil detail alamat...',
                    style: GoogleFonts.plusJakartaSans(fontSize: 11, color: Colors.grey.shade600),
                  ),
                ],
              )
            else if (_selectedAddress.isNotEmpty)
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: Colors.pink.shade50.withOpacity(0.5),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Icon(Icons.pin_drop, color: AppTheme.primaryPink, size: 16),
                    const SizedBox(width: 6),
                    Expanded(
                      child: Text(
                        _selectedAddress,
                        style: GoogleFonts.plusJakartaSans(
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                          color: AppTheme.textDark,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
              ),

            const SizedBox(height: 16),

            // Tombol Konfirmasi Pilihan
            SizedBox(
              width: double.infinity,
              height: 42,
              child: ElevatedButton.icon(
                onPressed: _selectedAddress.isEmpty
                    ? null
                    : () {
                        Navigator.pop(context, _selectedAddress);
                      },
                icon: const Icon(Icons.check_circle_outline, size: 18),
                label: Text(
                  'Gunakan Lokasi Ini',
                  style: GoogleFonts.plusJakartaSans(
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.primaryPink,
                  foregroundColor: Colors.white,
                  elevation: 0,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildQuickPickChip(String kota) {
    return Padding(
      padding: const EdgeInsets.only(right: 6),
      child: InkWell(
        onTap: () {
          _searchCtrl.text = kota;
          _cariAlamat(kota);
        },
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppTheme.borderPink),
          ),
          child: Text(
            kota,
            style: GoogleFonts.plusJakartaSans(
              fontSize: 9.5,
              fontWeight: FontWeight.w600,
              color: AppTheme.primaryPink,
            ),
          ),
        ),
      ),
    );
  }
}
