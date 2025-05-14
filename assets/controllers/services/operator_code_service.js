import { getSettingsData } from '../../js/server-variable';
import axios from 'axios';

// Default values
let codeOpeRegex = /^\d{5}$/;
let codeOpeMethodBool = false;


/**
 * Operator Code Service - Provides methods for code generation, validation, and checking
 */
class OperatorCodeService {
    constructor() {
        console.log('OperatorCodeService: Initializing service');
        this.initialized = false;
        this.initPromise = null;
    }




    /**
      * Start the initialization process
      * This should be called right after creating the service instance
      */
    init() {
        if (this.initPromise === null || this.initPromise === undefined) {
            console.log('OperatorCodeService: Starting initialization');
            this.initPromise = this.initialize();
        }
        return this.initPromise;
    }




    /**
     * Initialize the service with settings from the server
     * @private
     */
    async initialize() {
        try {
            console.log('OperatorCodeService: Fetching settings from server');
            const data = await getSettingsData();
            console.log('OperatorCodeService: Initialized with settings:', data);
            codeOpeMethodBool = data.operatorCodeMethod;
            console.log('OperatorCodeService: Using arithmetic method:', codeOpeMethodBool);
            if (!codeOpeMethodBool) {
                const regexPattern = data.operatorCodeRegex.replace(/^\/|\/$/g, '');
                codeOpeRegex = new RegExp(regexPattern);
                console.log('OperatorCodeService: Using custom regex pattern:', codeOpeRegex);
            } else {
                console.log('OperatorCodeService: Using default regex pattern for format validation:', codeOpeRegex);
            }
            this.initialized = true;
            console.log('OperatorCodeService: Initialization complete');
        } catch (error) {
            console.error('OperatorCodeService: Error initializing service:', error);
            console.log('OperatorCodeService: Falling back to default values');
            // Fall back to defaults
            this.initialized = true; // Mark as initialized even with defaults
        }
    }




    /**
     * Ensure the service is initialized before using it
     */
    async ensureInitialized() {
        if (!this.initialized) {
            console.log('OperatorCodeService: Service not initialized, waiting for initialization');
            if (this.initPromise === null || this.initPromise === undefined) {
                console.log('OperatorCodeService: No initialization in progress, starting now');
                this.initPromise = this.initialize();
            }
            await this.initPromise;
            console.log('OperatorCodeService: Service now initialized');
        }
    }




    /**
     * Generate a compliant operator code
     * @returns {Promise<string>} A 5-digit code
     */
    async #generateCode() {
        console.log('OperatorCodeService: Generating new code');
        await this.ensureInitialized();

        try {
            // Generate a random integer between 1 and 999
            const code = Math.floor(1 + Math.random() * 999);
            console.log('OperatorCodeService: Generated random base code:', code);

            // Sum the digits of the 'code' integer
            let sumOfDigits = code
                .toString()
                .split('')
                .reduce((sum, digit) => sum + Number(digit), 0);
            console.log('OperatorCodeService: Sum of digits in base code:', sumOfDigits);

            const sumOfDigitsString = sumOfDigits.toString();

            if (sumOfDigitsString.length < 2) {
                sumOfDigits = '0' + sumOfDigits;
                console.log('OperatorCodeService: Padded sum to two digits:', sumOfDigits);
            }

            // Combine the original code and the sum of its digits
            let newCode = code.toString() + sumOfDigits.toString();
            console.log('OperatorCodeService: Combined code before formatting:', newCode);

            // Ensure 'newCode' has exactly 5 digits
            if (newCode.length < 5) {
                // Pad with leading zeros if less than 5 digits
                newCode = newCode.padStart(5, '0');
                console.log('OperatorCodeService: Padded code to 5 digits:', newCode);
            } else if (newCode.length > 5) {
                // If more than 5 digits, use the last 5 digits
                newCode = newCode.slice(-5);
                console.log('OperatorCodeService: Trimmed code to 5 digits:', newCode);
            }

            console.log('OperatorCodeService: Final generated code:', newCode);
            return Promise.resolve(newCode);

        } catch (error) {
            console.error('OperatorCodeService: Error generating code:', error);
            return Promise.resolve(false);
        }
    }



    /**
     * Validate a code against the current regex pattern
     * @param {string} code - The code to validate
     * @returns {Promise<boolean>} Whether the code is valid
     */
    async validateCode(code) {
        console.log('OperatorCodeService: Validating code:', code);
        await this.ensureInitialized();

        if (!code || typeof code !== 'string') {
            console.log('OperatorCodeService: Invalid input, code must be a non-empty string');
            return Promise.resolve(false);
        }

        if (codeOpeMethodBool) {
            console.log('OperatorCodeService: Using arithmetic validation method');
            return this.validateCodeArithmetic(code);
        }

        try {
            console.log('OperatorCodeService::validateCode: regex used:', codeOpeRegex);
            const result = codeOpeRegex.test(code);
            console.log('OperatorCodeService::validateCode: Regex validation result:', result);
            return Promise.resolve(result);
        } catch (error) {
            console.error('OperatorCodeService: Error during regex validation:', error);
            return Promise.resolve(false);
        }
    }




    /**
     * Validate code against the arithmetic method where sum of first 3 digits equals last 2 digits
     * @param {string} code - The code to validate
     * @returns {Promise<boolean>} Whether the code is valid
     */
    async validateCodeArithmetic(code) {
        console.log('OperatorCodeService: Performing arithmetic validation on code:', code);
        try {
            if (!codeOpeRegex.test(code)) {
                console.log('OperatorCodeService: Code failed basic format validation');
                return Promise.resolve(false);
            }

            // Extract first 3 digits and calculate their sum
            const sumOfFirstThreeDigits = code
                .toString()
                .split('')
                .slice(0, 3)
                .reduce((sum, digit) => sum + Number(digit), 0);
            console.log('OperatorCodeService: Sum of first 3 digits:', sumOfFirstThreeDigits);

            // Extract last 2 digits as a single number
            const valueOfLastTwoDigits = Number(code.toString().slice(3));
            console.log('OperatorCodeService: Value of last 2 digits:', valueOfLastTwoDigits);

            // Check if the sum equals the last 2 digits
            const result = sumOfFirstThreeDigits === valueOfLastTwoDigits;
            console.log('OperatorCodeService: Arithmetic validation result:', result);
            return Promise.resolve(result);
        } catch (error) {
            console.error('OperatorCodeService: Error during arithmetic validation:', error);
            return Promise.resolve(false);
        }
    }




    /**
     * Check if a code already exists in the database
     * @param {string} code - The code to check
     * @returns {Promise<boolean>} Whether the code exists
     */
    async checkIfCodeExists(code) {
        console.log('OperatorCodeService: Checking if code exists in database:', code);
        try {
            const response = await axios.post('/docauposte/operator/check-if-code-exist', { code: code });
            console.log('OperatorCodeService: Code existence check response:', response.data);
            return response.data.found;
        } catch (error) {
            console.error('OperatorCodeService: Error checking if code exists:', error);
            return false;
        }
    }



    /**
     * Generate a unique code that doesn't exist in the database
     * @returns {Promise<string>} A unique operator code
     */
    async generateUniqueCode() {
        await this.ensureInitialized();
        console.log('OperatorCodeService: Generating unique code');
        let code = await this.#generateCode();
        console.log('OperatorCodeService: Generated initial code:', code);

        let exists = await this.checkIfCodeExists(code);
        console.log('OperatorCodeService: Code exists in database:', exists);

        // Keep generating until we find a code that doesn't exist
        let attempts = 0;
        const maxAttempts = 10; // Prevent infinite loops

        while (exists && attempts < maxAttempts) {
            console.log(`OperatorCodeService: Attempt ${attempts + 1} - Code already exists, generating new code`);
            code = await this.#generateCode();
            console.log('OperatorCodeService: Generated new code:', code);

            exists = await this.checkIfCodeExists(code);
            console.log('OperatorCodeService: New code exists in database:', exists);

            attempts++;
        }

        if (attempts >= maxAttempts) {
            console.error('OperatorCodeService: Failed to generate a unique code after multiple attempts');
        } else {
            console.log('OperatorCodeService: Successfully generated unique code:', code);
        }

        return code;
    }

    /**
     * Validate a code and check if it exists
     * @param {string} code - The code to validate
     * @returns {Promise<Object>} Validation result with isValid and exists properties
     */
    async validateAndCheckCode(code) {
        console.log('OperatorCodeService: Validating and checking code:', code);

        const isValid = await this.validateCode(code);
        console.log('OperatorCodeService: Code is valid:', isValid);

        let exists = false;

        if (isValid) {
            console.log('OperatorCodeService: Code is valid, checking if it exists');
            exists = await this.checkIfCodeExists(code);
            console.log('OperatorCodeService: Code exists:', exists);
        }

        const result = { isValid, exists };
        console.log('OperatorCodeService: Validation and check result:', result);
        return result;
    }



    /**
     * Get the current settings
     * @returns {Promise<Object>} Current settings
     */
    async getSettings() {
        await this.ensureInitialized();
        console.log('OperatorCodeService: Getting current settings');
        console.log('OperatorCodeService: Using default regex pattern:', codeOpeRegex);
        console.log('OperatorCodeService: Using arithmetic validation method:', codeOpeMethodBool);
        const settings = {
            regex: codeOpeRegex,
            methodEnabled: codeOpeMethodBool
        };

        console.log('OperatorCodeService: Current settings:', settings);
        return settings;
    }
}

// Create a singleton instance
const operatorCodeService = new OperatorCodeService();
console.log('OperatorCodeService: Singleton instance created');
// Initialize the service immediately
operatorCodeService.init().catch(error => {
    console.error('OperatorCodeService: Failed to initialize service:', error);
});
// Export the singleton service for use in other controllers
export { operatorCodeService };