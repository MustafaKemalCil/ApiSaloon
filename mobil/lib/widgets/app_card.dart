import 'package:flutter/material.dart';

enum AppCardType { normal, highlighted, warning }

class AppCard extends StatelessWidget {
  final String? title;
  final String? subtitle;
  final Widget? leading;
  final Widget? trailing;
  final VoidCallback? onTap;
  final Widget? child; // <-- child parametresi eklendi
  final AppCardType type;

  const AppCard({
    super.key,
    this.title,
    this.subtitle,
    this.leading,
    this.trailing,
    this.onTap,
    this.child,
    this.type = AppCardType.normal,
  });

  @override
  Widget build(BuildContext context) {
    // Kart tipine göre renk ve gölge
    Color bgColor;
    double elevation;

    switch (type) {
      case AppCardType.highlighted:
        bgColor = Colors.orange.shade100;
        elevation = 6;
        break;
      case AppCardType.warning:
        bgColor = Colors.red.shade100;
        elevation = 6;
        break;
      default:
        bgColor = Colors.white;
        elevation = 4;
    }

    return Card(
      color: bgColor,
      elevation: elevation,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      margin: const EdgeInsets.symmetric(vertical: 8, horizontal: 12),
      child:
          child ??
          ListTile(
            contentPadding: const EdgeInsets.all(16),
            leading: leading,
            trailing: trailing,
            title: title != null
                ? Text(
                    title!,
                    style: const TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 18,
                    ),
                  )
                : null,
            subtitle: subtitle != null
                ? Text(
                    subtitle!,
                    style: const TextStyle(fontSize: 14, color: Colors.grey),
                  )
                : null,
            onTap: onTap,
          ),
    );
  }
}
