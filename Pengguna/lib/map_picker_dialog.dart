import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:http/http.dart' as http;
import 'package:latlong2/latlong.dart';
import 'theme.dart';

class MapPickerDialog extends StatefulWidget {
  final String initialLocation;
  const MapPickerDialog({super.key, this.initialLocation = ''});

  @override
  State<MapPickerDialog> createState() => _MapPickerDialogState();
}

class _MapPickerDialogState extends State<MapPickerDialog> {
  final TextEditingController _searchCtrl = TextEditingController();
  final MapController _mapController = MapController();
  List<dynamic> _searchResults = [];
  bool _isSearching = false;
  bool _isGeocoding = false;
  Timer? _reverseGeocodeTimer;
  int _searchRequestId = 0;
  int _reverseRequestId = 0;

  double _lat = -8.1724;
  double _lng = 113.7003;
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
    _reverseGeocodeTimer?.cancel();
    _searchCtrl.dispose();
    super.dispose();
  }

  Future<void> _reverseGeocode(double lat, double lng) async {
    final requestId = ++_reverseRequestId;
    setState(() => _isGeocoding = true);
    try {
      final url = Uri.https('nominatim.openstreetmap.org', '/reverse', {
        'format': 'jsonv2',
        'lat': lat.toStringAsFixed(6),
        'lon': lng.toStringAsFixed(6),
        'zoom': '18',
        'addressdetails': '1',
      });
      final response = await http.get(url, headers: {
        'User-Agent': 'SahabatPPA_FlutterApp/1.0 (com.example.pengguna1)',
        'Accept': 'application/json',
      }).timeout(const Duration(seconds: 12));
      if (response.statusCode != 200) {
        throw Exception('Alamat tidak ditemukan');
      }
      final data = jsonDecode(response.body) as Map<String, dynamic>;
      if (!mounted || requestId != _reverseRequestId) return;
      final address = data['display_name']?.toString();
      setState(() {
        _selectedAddress = address?.isNotEmpty == true
            ? address!
            : 'Koordinat ${lat.toStringAsFixed(6)}, ${lng.toStringAsFixed(6)}';
        _searchCtrl.text = _selectedAddress;
      });
    } catch (_) {
      if (!mounted || requestId != _reverseRequestId) return;
      setState(() {
        _selectedAddress =
            'Koordinat ${lat.toStringAsFixed(6)}, ${lng.toStringAsFixed(6)}';
        _searchCtrl.text = _selectedAddress;
      });
    } finally {
      if (mounted && requestId == _reverseRequestId) {
        setState(() => _isGeocoding = false);
      }
    }
  }

  Future<void> _cariAlamat(String query) async {
    final trimmedQuery = query.trim();
    if (trimmedQuery.isEmpty) return;
    final requestId = ++_searchRequestId;
    setState(() => _isSearching = true);
    try {
      final url = Uri.https('nominatim.openstreetmap.org', '/search', {
        'format': 'jsonv2',
        'q': trimmedQuery,
        'limit': '5',
        'countrycodes': 'id',
        'addressdetails': '1',
      });
      final response = await http.get(url, headers: {
        'User-Agent': 'SahabatPPA_FlutterApp/1.0 (com.example.pengguna1)',
        'Accept': 'application/json',
      }).timeout(const Duration(seconds: 12));
      if (response.statusCode != 200) {
        throw Exception('Pencarian lokasi gagal');
      }
      final data = jsonDecode(response.body) as List<dynamic>;
      if (!mounted || requestId != _searchRequestId) return;
      setState(() => _searchResults = data);
    } catch (_) {
      if (!mounted || requestId != _searchRequestId) return;
      setState(() => _searchResults = []);
    } finally {
      if (mounted && requestId == _searchRequestId) {
        setState(() => _isSearching = false);
      }
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
    _mapController.move(LatLng(lat, lon), 16);
  }

  void _pilihTitik(LatLng point) {
    setState(() {
      _lat = point.latitude;
      _lng = point.longitude;
      _selectedAddress = '';
    });
    _mapController.move(point, 16);
    _reverseGeocode(point.latitude, point.longitude);
  }

  void _posisiPetaBerubah(MapCamera camera, bool hasGesture) {
    if (!hasGesture) return;
    final center = camera.center;
    if ((_lat - center.latitude).abs() < 0.00001 &&
        (_lng - center.longitude).abs() < 0.00001) {
      return;
    }

    setState(() {
      _lat = center.latitude;
      _lng = center.longitude;
      _selectedAddress = '';
    });
    _reverseGeocodeTimer?.cancel();
    _reverseGeocodeTimer = Timer(const Duration(milliseconds: 800), () {
      _reverseGeocode(_lat, _lng);
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
                    const Icon(Icons.map_outlined,
                        color: AppTheme.primaryPink, size: 22),
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
                hintStyle: GoogleFonts.plusJakartaSans(
                    fontSize: 11, color: Colors.grey.shade400),
                prefixIcon: const Icon(Icons.search,
                    color: AppTheme.primaryPink, size: 18),
                suffixIcon: _isSearching
                    ? const Padding(
                        padding: EdgeInsets.all(12),
                        child: SizedBox(
                          width: 14,
                          height: 14,
                          child: CircularProgressIndicator(
                              strokeWidth: 2, color: AppTheme.primaryPink),
                        ),
                      )
                    : IconButton(
                        icon: const Icon(Icons.arrow_forward,
                            color: AppTheme.primaryPink, size: 18),
                        onPressed: () => _cariAlamat(_searchCtrl.text),
                      ),
                contentPadding:
                    const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(color: AppTheme.borderPink),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide:
                      const BorderSide(color: AppTheme.primaryPink, width: 1.5),
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
                      leading: const Icon(Icons.location_on,
                          color: AppTheme.primaryPink, size: 16),
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

            // Peta interaktif dengan pin tetap di tengah saat peta digeser.
            Container(
              height: 260,
              width: double.infinity,
              clipBehavior: Clip.antiAlias,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: AppTheme.borderPink),
              ),
              child: Stack(
                children: [
                  FlutterMap(
                    mapController: _mapController,
                    options: MapOptions(
                      initialCenter: LatLng(_lat, _lng),
                      initialZoom: 14,
                      minZoom: 3,
                      maxZoom: 19,
                      onTap: (_, point) => _pilihTitik(point),
                      onPositionChanged: _posisiPetaBerubah,
                    ),
                    children: [
                      TileLayer(
                        urlTemplate:
                            'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                        userAgentPackageName: 'com.example.pengguna1',
                        maxZoom: 19,
                      ),
                      const RichAttributionWidget(
                        attributions: [
                          TextSourceAttribution('OpenStreetMap contributors'),
                        ],
                      ),
                    ],
                  ),
                  Positioned.fill(
                    child: IgnorePointer(
                      child: Center(
                        child: Transform.translate(
                          offset: const Offset(0, -18),
                          child: const Icon(
                            Icons.location_on,
                            color: AppTheme.primaryPink,
                            size: 42,
                            shadows: [
                              Shadow(color: Colors.white, blurRadius: 5),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ),
                  Positioned(
                    top: 8,
                    left: 8,
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 9,
                        vertical: 6,
                      ),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.95),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        'Geser peta atau ketuk titik lokasi',
                        style: GoogleFonts.plusJakartaSans(
                          fontSize: 10,
                          fontWeight: FontWeight.w600,
                          color: AppTheme.textDark,
                        ),
                      ),
                    ),
                  ),
                  Positioned(
                    top: 8,
                    right: 8,
                    child: Material(
                      color: Colors.white,
                      shape: const CircleBorder(),
                      elevation: 2,
                      child: IconButton(
                        tooltip: 'Perbesar peta',
                        visualDensity: VisualDensity.compact,
                        onPressed: () => _mapController.move(
                          LatLng(_lat, _lng),
                          16,
                        ),
                        icon: const Icon(Icons.my_location_outlined, size: 18),
                        color: AppTheme.primaryPink,
                      ),
                    ),
                  ),
                  Positioned(
                    bottom: 8,
                    left: 8,
                    child: SingleChildScrollView(
                      scrollDirection: Axis.horizontal,
                      child: Row(
                        children: [
                          _buildQuickPickChip('Jember'),
                          _buildQuickPickChip('Jakarta'),
                          _buildQuickPickChip('Bandung'),
                          _buildQuickPickChip('Surabaya'),
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
                    child: CircularProgressIndicator(
                        strokeWidth: 2, color: AppTheme.primaryPink),
                  ),
                  const SizedBox(width: 8),
                  Text(
                    'Mengambil detail alamat...',
                    style: GoogleFonts.plusJakartaSans(
                        fontSize: 11, color: Colors.grey.shade600),
                  ),
                ],
              )
            else if (_selectedAddress.isNotEmpty)
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: Colors.pink.shade50.withValues(alpha: 0.5),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Icon(Icons.pin_drop,
                        color: AppTheme.primaryPink, size: 16),
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
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10)),
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
