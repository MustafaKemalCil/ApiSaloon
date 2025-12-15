import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../global.dart';
import '../Login/index.dart';
import 'package:http/http.dart' as http;
import 'package:mobil/widgets/widgets.dart';
import 'dart:convert'; // jsonEncode ve jsonDecode için
import 'package:url_launcher/url_launcher.dart';
import 'package:dio/dio.dart';

class MainLayout extends StatefulWidget {
  final Widget child; // Her sayfanın içeriği buraya gelecek

  const MainLayout({super.key, required this.child});

  @override
  State<MainLayout> createState() => _MainLayoutState();
}

class _MainLayoutState extends State<MainLayout> {
  @override
  void initState() {
    super.initState();
    _checkToken();
    // 🚀 Sürüm kontrolü burada tetikleniyor
    WidgetsBinding.instance.addPostFrameCallback((_) {
      checkAppVersion(context);
    });
  }

  Future<void> checkAppVersion(BuildContext context) async {
    final url = Uri.parse("$apiBaseUrl/api/app-version/latest");

    try {
      final response = await http.post(
        url,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'platform': 'android',
          'version': MOBILE_APP_VERSION,
        }),
      );

      final data = jsonDecode(response.body);
      print("Gönderilen veri: ${jsonEncode(data)}");

      if (response.statusCode == 200) {
        if (data['message'] == "You are using the latest version") {
          print("Kullanıcı en güncel sürümde.");
          return;
        }

        // Backend'den gelen dosya yolu
        final filePath = data['file_path'] ?? data['url'] ?? '';

        if (filePath.isEmpty) {
          print("Dosya yolu yok!");
          return;
        }

        // Eğer path tam URL değilse base URL ekle
        final downloadUrl = "$apiBaseUrl$filePath";

        // Güncelleme popup
        showDialog(
          context: context,
          builder: (ctx) => AlertDialog(
            title: Text("Güncelleme Mevcut"),
            content: Text(
              "Yeni sürüm: ${data['version']} mevcut.\n\nGüncellemek ister misiniz?",
            ),
            actions: [
              TextButton(
                onPressed: () {
                  Navigator.of(ctx).pop();
                },
                child: Text("İptal"),
              ),
              TextButton(
                onPressed: () async {
                  Navigator.of(ctx).pop();
                  final uri = Uri.parse(downloadUrl);
                  if (await canLaunchUrl(uri)) {
                    await launchUrl(uri, mode: LaunchMode.externalApplication);
                  } else {
                    print("URL açılamadı: $downloadUrl");
                  }
                },
                child: Text("Güncelle"),
              ),
            ],
          ),
        );
      } else {
        print("Mesaj: ${data['message']}");
      }
    } catch (e) {
      print("Hata: $e");
    }
  }

  Future<void> _checkToken() async {
    final prefs = await SharedPreferences.getInstance();
    final savedToken = prefs.getString("token");

    if ((globalToken == null || globalToken!.isEmpty) &&
        (savedToken == null || savedToken.isEmpty)) {
      if (mounted) {
        Navigator.pushAndRemoveUntil(
          context,
          MaterialPageRoute(builder: (_) => const LoginPage()),
          (route) => false,
        );
      }
    } else if (globalToken == null || globalToken!.isEmpty) {
      globalToken = savedToken;
    }
  }

  Future<void> _logout() async {
    try {
      if (globalToken != null && globalToken!.isNotEmpty) {
        final url = Uri.parse("$apiBaseUrl/api/logout"); // backend URL
        final response = await http.post(
          url,
          headers: {
            "Accept": "application/json",
            "Content-Type": "application/json",
            "Authorization": "Bearer $globalToken",
          },
        );

        if (response.statusCode == 200) {
          print("Server logout başarılı");
        } else {
          print("Server logout hatası: ${response.body}");
        }
      }
    } catch (e) {
      print("Logout isteği sırasında hata: $e");
    }

    // 1️⃣ Global token temizle
    globalToken = null;

    // 2️⃣ SharedPreferences’tan token sil
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove("token");

    // 3️⃣ Login sayfasına yönlendir
    if (mounted) {
      Navigator.pushAndRemoveUntil(
        context,
        MaterialPageRoute(builder: (_) => const LoginPage()),
        (route) => false,
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text("Beauty Panel"),
        backgroundColor: Colors.purple,
      ),
      drawer: Drawer(
        child: ListView(
          padding: EdgeInsets.zero,
          children: [
            const DrawerHeader(
              decoration: BoxDecoration(color: Colors.purple),
              child: Text(
                "Menü",
                style: TextStyle(color: Colors.white, fontSize: 20),
              ),
            ),
            // Anasayfa
            ExpansionTile(
              leading: const Icon(Icons.people),
              title: const Text("Çalışanlar"),
              children: [
                ListTile(
                  leading: const Icon(Icons.home),
                  title: const Text("Çalışan_oluştur"),
                  onTap: () {
                    Navigator.pushReplacementNamed(context, "/employee");
                  },
                ),
                ListTile(
                  leading: const Icon(Icons.home),
                  title: const Text("Çalışan Listele"),
                  onTap: () {
                    Navigator.pushReplacementNamed(context, "/employee_list");
                  },
                ),
              ],
            ),
            // Randevular
            ExpansionTile(
              leading: const Icon(Icons.dashboard),
              title: const Text("Randevular"),
              children: [
                ListTile(
                  title: const Text("Randevu oluştur"),
                  onTap: () {
                    Navigator.pushReplacementNamed(context, "/appointments");
                  },
                ),
              ],
            ),
            // Dashboard menüsü
            ExpansionTile(
              leading: const Icon(Icons.dashboard),
              title: const Text("Dashboard"),
              children: [
                ListTile(
                  title: const Text("Randevu Takvimi"),
                  onTap: () {
                    Navigator.pushReplacementNamed(context, "/dashboard");
                  },
                ),
              ],
            ),
            // Service menüsü
            ExpansionTile(
              leading: const Icon(Icons.dashboard),
              title: const Text("Hizmetler"),
              children: [
                ListTile(
                  title: const Text("Hizmetler"),
                  onTap: () {
                    Navigator.pushReplacementNamed(context, "/service");
                  },
                ),
              ],
            ),
            // Profil menüsü
            ExpansionTile(
              leading: const Icon(Icons.person),
              title: const Text("Profil"),
              children: [
                ListTile(
                  title: const Text("Profil Bilgileri"),
                  onTap: () {
                    Navigator.pushReplacementNamed(context, "/profile");
                  },
                ),
              ],
            ),

            // Müşteriler menüsü
            ExpansionTile(
              leading: const Icon(Icons.people),
              title: const Text("Müşteriler"),
              children: [
                ListTile(
                  title: const Text("Müşteriler"),
                  onTap: () {
                    Navigator.pushReplacementNamed(context, "/customers");
                  },
                ),
                ListTile(
                  title: const Text("Müşteri Ekle"),
                  onTap: () {
                    Navigator.pushReplacementNamed(
                      context,
                      "/customers_profile",
                    );
                  },
                ),
              ],
            ),

            // Çıkış
            ListTile(
              leading: const Icon(Icons.logout),
              title: const Text("Çıkış Yap"),
              onTap: _logout,
            ),
          ],
        ),
      ),

      // 🌟 DashboardPage’deki Column buraya child olarak gelecek
      body: SafeArea(child: widget.child),
    );
  }
}
