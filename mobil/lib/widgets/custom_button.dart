import 'package:flutter/material.dart';

enum AppButtonType { primary, secondary, danger }

class AppButton extends StatelessWidget {
  final String text;
  final VoidCallback onPressed;
  final AppButtonType type;
  final double borderRadius;
  final double height;
  final double fontSize;
  final Icon? icon;

  const AppButton({
    super.key,
    required this.text,
    required this.onPressed,
    this.type = AppButtonType.primary,
    this.borderRadius = 12,
    this.height = 50,
    this.fontSize = 16,
    this.icon,
  });

  @override
  Widget build(BuildContext context) {
    // Buton tipine göre renkleri belirle
    Color bgColor;
    Color textColor;

    switch (type) {
      case AppButtonType.secondary:
        bgColor = Colors.grey.shade300;
        textColor = Colors.black;
        break;
      case AppButtonType.danger:
        bgColor = Colors.red.shade400;
        textColor = Colors.white;
        break;
      default: // primary
        bgColor = Colors.blue.shade600;
        textColor = Colors.white;
    }

    return SizedBox(
      height: height,
      child: ElevatedButton(
        style: ElevatedButton.styleFrom(
          backgroundColor: bgColor,
          foregroundColor: textColor,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(borderRadius),
          ),
          textStyle: TextStyle(fontSize: fontSize, fontWeight: FontWeight.bold),
        ),
        onPressed: onPressed,
        child: icon == null
            ? Text(text)
            : Row(
                mainAxisSize: MainAxisSize.min,
                children: [icon!, const SizedBox(width: 8), Text(text)],
              ),
      ),
    );
  }
}
